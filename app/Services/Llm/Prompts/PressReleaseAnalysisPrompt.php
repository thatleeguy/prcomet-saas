<?php

namespace App\Services\Llm\Prompts;

use App\Models\PressRelease;
use App\Services\Llm\LlmClient;
use RuntimeException;

/**
 * Extract structured features from a press release: entities, topics, stance,
 * and the headline-worthy key claims an outreach pitch could anchor on.
 *
 * Output is strict JSON to keep parsing deterministic. The prompt is versioned
 * here (in code) so we can grep + diff prompt changes alongside the data they
 * affect.
 */
class PressReleaseAnalysisPrompt
{
    public const VERSION = 'v1.0';

    public static function run(LlmClient $llm, PressRelease $release): array
    {
        $body = $release->body_text ?: strip_tags($release->body_html ?? '');
        // Truncate to keep token use predictable — most PRs are under this.
        $body = mb_substr($body, 0, 12000);

        $system = self::systemPrompt();
        $user = self::userMessage($release->title, $body);

        $raw = $llm->complete($system, $user, [
            'max_tokens' => 2048,
            'temperature' => 0,
        ]);

        return self::parse($raw);
    }

    public static function systemPrompt(): string
    {
        return <<<'PROMPT'
You analyze press releases from publicly traded mining companies for an outreach-discovery tool.
Your output must be valid JSON matching this schema exactly:

{
  "entities": {
    "commodities": [string],   // e.g. "gold", "copper", "lithium"
    "jurisdictions": [string], // e.g. "Nevada", "British Columbia"
    "projects": [string],      // named project/asset names
    "people": [string]         // people named in the release
  },
  "topics": [string],          // 3-8 topical tags, lowercase, hyphenated
  "stance": "bullish"|"bearish"|"neutral"|"informational",
  "key_claims": [
    {
      "claim": string,         // a concise factual claim from the release
      "type": "result"|"financing"|"corporate"|"guidance"|"other"
    }
  ]
}

Rules:
- Only extract what is explicitly stated. Do not infer or speculate.
- "stance" reflects the tone of the release itself, not your opinion.
- Output only the JSON object, no surrounding prose.
- If the release contains no relevant content, return the schema with empty arrays.
PROMPT;
    }

    public static function userMessage(string $title, string $body): string
    {
        return "TITLE: {$title}\n\nBODY:\n{$body}";
    }

    /**
     * @return array{entities: array, topics: array, stance: ?string, key_claims: array}
     */
    public static function parse(string $raw): array
    {
        $json = self::extractJson($raw);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new RuntimeException('LLM returned non-JSON response: '.mb_substr($raw, 0, 200));
        }

        return [
            'entities' => $data['entities'] ?? [],
            'topics' => $data['topics'] ?? [],
            'stance' => $data['stance'] ?? null,
            'key_claims' => $data['key_claims'] ?? [],
        ];
    }

    /**
     * Strip code fences and stray prose around a JSON object response.
     */
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
