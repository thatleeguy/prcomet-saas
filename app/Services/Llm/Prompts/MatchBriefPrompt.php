<?php

namespace App\Services\Llm\Prompts;

use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Services\Llm\LlmClient;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Stage 2 of matching: ask the LLM to rank candidates by receptivity to the
 * company's press release, and produce a brief (rationale + suggested angle
 * + citations) for the top picks.
 *
 * Uses the high-stakes model by default — the brief is THE product output
 * and quality justifies the cost. Token use stays bounded because we pre-
 * filter to ~20 candidates upstream.
 */
class MatchBriefPrompt
{
    public const VERSION = 'v1.0';

    /**
     * @param  Collection<int, PublicationItem>  $candidates
     * @return array<int, array{publication_item_id:int, score:float, rationale:string, suggested_angle:string, citations:array}>
     */
    public static function run(
        LlmClient $llm,
        PressRelease $release,
        Collection $candidates,
        int $topN = 5,
    ): array {
        if ($candidates->isEmpty()) {
            return [];
        }

        $system = self::systemPrompt($topN);
        $user = self::userMessage($release, $candidates);

        $raw = $llm->complete($system, $user, [
            'model' => config('services.anthropic.high_stakes_model', 'claude-opus-4-7'),
            'max_tokens' => 4000,
            'temperature' => 0,
        ]);

        return self::parse($raw, $candidates);
    }

    private static function systemPrompt(int $topN): string
    {
        return <<<PROMPT
You are an outreach-discovery analyst. A junior mining company has published a press release; you have a list of recent articles, podcast episodes, and posts from mining-focused journalists, hosts, and writers.

Your job: from the candidates, pick up to {$topN} who would be most RECEPTIVE to hearing about this press release, and for each produce a concise brief.

Receptivity criteria (use them all, don't anchor on any one):
- Topic overlap: do they cover this commodity, region, or company type?
- Stance fit: does this PR fit (or productively contrast) their recent stance?
- Recency: more weight to candidates writing on relevant topics in the last 30 days.
- Track record: if a candidate made specific predictions that were correct, mention it — that's leverage for outreach.

For each pick, output:
- `publication_item_id`: integer (from the candidate list)
- `score`: 0.0 to 1.0 — your confidence in this match (be calibrated; don't inflate)
- `rationale`: 2-4 sentences. Cite specific articles/episodes by date and topic. Never speculate beyond what's in the candidate context.
- `suggested_angle`: 1-2 sentences. A concrete angle the CEO/team could pitch — counter-story, podcast appearance, follow-up.
- `citations`: array of {publication_item_id, quote} — the specific candidate item(s) you reference in the rationale.

Output strict JSON: a top-level array of pick objects, ordered by score descending.
If no candidate clears a 0.5 confidence bar, return an empty array `[]` — quality over quantity.
No prose outside the JSON.
PROMPT;
    }

    /**
     * @param  Collection<int, PublicationItem>  $candidates
     */
    private static function userMessage(PressRelease $release, Collection $candidates): string
    {
        $entities = $release->extracted_entities ?? [];
        $commodities = implode(', ', $entities['commodities'] ?? []);
        $jurisdictions = implode(', ', $entities['jurisdictions'] ?? []);
        $topics = implode(', ', $release->extracted_topics ?? []);
        $claims = collect($release->key_claims ?? [])
            ->map(fn ($c) => '- '.($c['claim'] ?? ''))
            ->implode("\n");

        $pr = <<<PR
PRESS RELEASE
Company: {$release->company->name} ({$release->company->ticker})
Title: {$release->title}
Stance: {$release->stance}
Commodities: {$commodities}
Jurisdictions: {$jurisdictions}
Topics: {$topics}
Key claims:
{$claims}

PR;

        $candidateLines = $candidates->map(function (PublicationItem $item) {
            $author = $item->author?->name ?? 'Unknown';
            $sourceName = $item->source?->name ?? '';
            $date = optional($item->published_at)->toDateString() ?? 'undated';
            $topics = implode(', ', $item->extracted_topics ?? []);
            $authorProfile = $item->author?->body_of_work_summary
                ? "\n  Author profile: ".mb_substr($item->author->body_of_work_summary, 0, 500)
                : '';
            $snippet = mb_substr($item->body_text ?? '', 0, 400);

            return <<<C
ID: {$item->id}
  Source: {$sourceName}
  Author: {$author}
  Published: {$date}
  Stance: {$item->stance}
  Topics: {$topics}{$authorProfile}
  URL: {$item->url}
  Snippet: {$snippet}
C;
        })->implode("\n\n");

        return $pr."\n\nCANDIDATES:\n\n".$candidateLines;
    }

    /**
     * @param  Collection<int, PublicationItem>  $candidates
     * @return array<int, array>
     */
    private static function parse(string $raw, Collection $candidates): array
    {
        $json = self::extractJsonArray($raw);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new RuntimeException('LLM returned non-array response: '.mb_substr($raw, 0, 200));
        }

        $allowedIds = $candidates->pluck('id')->all();

        $picks = [];
        foreach ($data as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = (int) ($row['publication_item_id'] ?? 0);
            if (! in_array($id, $allowedIds, true)) {
                continue; // ignore IDs the model hallucinated
            }
            $picks[] = [
                'publication_item_id' => $id,
                'score' => (float) ($row['score'] ?? 0),
                'rationale' => trim((string) ($row['rationale'] ?? '')),
                'suggested_angle' => trim((string) ($row['suggested_angle'] ?? '')),
                'citations' => is_array($row['citations'] ?? null) ? $row['citations'] : [],
            ];
        }

        return $picks;
    }

    private static function extractJsonArray(string $raw): string
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $raw = preg_replace('/\s*```$/', '', $raw);

        $first = strpos($raw, '[');
        $last = strrpos($raw, ']');

        if ($first === false || $last === false) {
            return $raw;
        }

        return substr($raw, $first, $last - $first + 1);
    }
}
