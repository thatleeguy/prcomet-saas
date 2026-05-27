<?php

namespace App\Services\Llm\Prompts;

use App\Models\Author;
use App\Services\Llm\LlmClient;

/**
 * Build a rolling body-of-work summary for an author from their recent items.
 * Drives match-receptivity rationale ("this writer covered lithium glut last
 * month and was directionally right; they'd be receptive to a low-strip-ratio
 * counterpoint").
 */
class AuthorProfilePrompt
{
    public const VERSION = 'v1.0';

    public static function run(LlmClient $llm, Author $author, int $maxItems = 20): string
    {
        $items = $author->items()
            ->orderByDesc('published_at')
            ->limit($maxItems)
            ->get(['title', 'published_at', 'extracted_topics', 'stance']);

        if ($items->isEmpty()) {
            return '';
        }

        $lines = $items->map(function ($item) {
            $topics = is_array($item->extracted_topics) ? implode(', ', $item->extracted_topics) : '';
            $when = optional($item->published_at)->toDateString() ?? 'undated';
            return "- {$when} [{$item->stance}] {$item->title} — topics: {$topics}";
        })->implode("\n");

        $system = <<<'PROMPT'
You write concise analyst-style profiles of journalists, podcast hosts, and newsletter authors covering the mining and commodities sector.

Given a list of an author's recent items (date, stance, title, topics), produce a 3-5 sentence profile capturing:
- Their primary coverage areas (commodities, regions, themes).
- Their general stance and whether it's consistent.
- Notable predictions or contrarian positions worth noting for outreach matching.

Be specific. Cite topic names. Avoid generic praise or hedging.
Output plain prose only — no JSON, no bullets, no preamble.
PROMPT;

        $user = "Author: {$author->name}\n\nRecent items:\n{$lines}";

        return $llm->complete($system, $user, [
            'max_tokens' => 600,
            'temperature' => 0.2,
        ]);
    }
}
