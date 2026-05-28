<?php

namespace App\Services\Llm\Prompts;

use App\Models\Watch;
use App\Models\WatchHit;
use App\Services\Llm\LlmClient;

/**
 * Ask Claude whether a snippet actually refers to the watched entity, or
 * whether it's a false positive on a coincidental string match.
 *
 * The job that calls this is downgrade-safe — if the prompt fails or
 * returns garbage, the hit stays with confirmed_by_llm = null and the UI
 * surfaces it as "pending". We never let an LLM failure block the user
 * from seeing literal matches.
 *
 * Uses the default model — cheap per-call, judgement is the kind of task
 * Sonnet handles fine.
 */
class WatchHitConfirmationPrompt
{
    public const VERSION = 'v1.0';

    /**
     * @return array{confirmed: bool, reasoning: string}|null  null when the
     *         response cannot be parsed; caller leaves the hit pending.
     */
    public static function run(LlmClient $llm, Watch $watch, WatchHit $hit): ?array
    {
        $system = self::systemPrompt();
        $user = self::userMessage($watch, $hit);

        $raw = $llm->complete($system, $user, [
            'model' => config('services.anthropic.default_model', 'claude-sonnet-4-6'),
            'max_tokens' => 200,
            'temperature' => 0,
        ]);

        return self::parse($raw);
    }

    private static function systemPrompt(): string
    {
        return <<<PROMPT
        You verify whether a short text snippet genuinely refers to a specific watched entity, or whether the literal string match is coincidental (a homonym, a substring, a different organisation with a similar name, etc.).

        The user will give you:
          - The watched entity's name and what kind of thing it is.
          - The list of literal terms / aliases they registered.
          - A snippet of text that contained one of those terms.

        Respond ONLY with a single JSON object on one line, no commentary:
          {"confirmed": true|false, "reasoning": "one sentence, under 200 chars"}

        Confirm only when you're confident the snippet is about the watched entity. When the match is ambiguous, prefer false and say so. The user can override.
        PROMPT;
    }

    private static function userMessage(Watch $watch, WatchHit $hit): string
    {
        $aliases = collect($watch->terms ?? [])->implode(', ');
        $snippet = $hit->context_snippet ?? '(no snippet)';

        return <<<MSG
        Watched entity: {$watch->name}
        Kind: {$watch->kind}
        Aliases registered: {$aliases}
        Matched term: {$hit->matched_term}

        Snippet:
        {$snippet}
        MSG;
    }

    /**
     * Parse the assistant's JSON. Returns null on any error — the caller
     * treats null as "leave it pending".
     */
    private static function parse(string $raw): ?array
    {
        $trimmed = trim($raw);
        // Tolerate a leading code fence even though we asked for none.
        $trimmed = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $trimmed);

        $decoded = json_decode($trimmed, true);
        if (! is_array($decoded) || ! array_key_exists('confirmed', $decoded)) {
            return null;
        }

        return [
            'confirmed' => (bool) $decoded['confirmed'],
            'reasoning' => substr((string) ($decoded['reasoning'] ?? ''), 0, 240),
        ];
    }
}
