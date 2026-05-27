<?php

namespace App\Services\Llm\Prompts;

use App\Models\PublicationItem;
use App\Services\Llm\LlmClient;
use RuntimeException;

/**
 * Extract structured features from a publication item (article, podcast
 * episode, substack post), and identify the author when possible. Adds
 * claims-with-predictions for downstream verification.
 *
 * Output JSON schema is documented in {@see systemPrompt}.
 */
class PublicationItemAnalysisPrompt
{
    public const VERSION = 'v1.0';

    public static function run(LlmClient $llm, PublicationItem $item): array
    {
        $body = $item->transcript ?: ($item->body_text ?: '');
        $body = mb_substr($body, 0, 16000);

        $system = self::systemPrompt();
        $user = "TITLE: {$item->title}\n\nBODY:\n{$body}";

        $raw = $llm->complete($system, $user, [
            'max_tokens' => 3000,
            'temperature' => 0,
        ]);

        return self::parse($raw);
    }

    public static function systemPrompt(): string
    {
        return <<<'PROMPT'
You analyze articles, podcast episode descriptions, and newsletters covering the mining and commodities space.
Output strict JSON matching this schema:

{
  "author": {
    "name": string|null,        // best-effort identification of the author or host
    "x_handle": string|null     // if mentioned
  },
  "entities": {
    "commodities": [string],
    "jurisdictions": [string],
    "companies": [string]       // any public mining companies discussed
  },
  "topics": [string],           // 3-8 topical tags, lowercase, hyphenated
  "stance": "bullish"|"bearish"|"neutral"|"contrarian"|"informational",
  "claims": [
    {
      "claim": string,          // a specific stated claim or prediction
      "topic": string,          // which commodity/area the claim relates to
      "predicted_outcome": string|null,  // if forward-looking, the predicted state
      "timeframe": string|null  // when the prediction should resolve ("Q1 2026", "by year-end")
    }
  ]
}

Rules:
- Only extract what is explicitly stated.
- For author identification, prefer bylines, episode hosts, or "by X" phrases. If unclear, return null.
- "claims" should capture testable assertions, not vague opinion.
- Output only the JSON object, no surrounding prose.
PROMPT;
    }

    /**
     * @return array{author: array, entities: array, topics: array, stance: ?string, claims: array}
     */
    public static function parse(string $raw): array
    {
        $json = self::extractJson($raw);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new RuntimeException('LLM returned non-JSON response: '.mb_substr($raw, 0, 200));
        }

        return [
            'author' => $data['author'] ?? [],
            'entities' => $data['entities'] ?? [],
            'topics' => $data['topics'] ?? [],
            'stance' => $data['stance'] ?? null,
            'claims' => $data['claims'] ?? [],
        ];
    }

    private static function extractJson(string $raw): string
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);

        $first = strpos($raw, '{');
        $last = strrpos($raw, '}');

        if ($first === false || $last === false) {
            return $raw;
        }

        return substr($raw, $first, $last - $first + 1);
    }
}
