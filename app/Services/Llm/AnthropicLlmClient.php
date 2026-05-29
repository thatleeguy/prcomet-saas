<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Claude (Anthropic Messages API) implementation.
 *
 * Defaults to Sonnet for bulk extraction work; Opus is opt-in via
 * $options['model'] for high-stakes paths (the match-brief generator).
 *
 * Budget guardrails: every call runs guardSystemBudget() first and
 * records actual token usage afterwards. When the system cap is hit,
 * complete() throws BudgetExceededException — callers handle that as
 * a soft failure so literal results stay visible to users.
 *
 * Configured via config/services.php → 'anthropic' and
 * config/llm.php (budgets + pricing).
 */
class AnthropicLlmClient implements LlmClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly LlmUsageTracker $tracker,
        private readonly string $defaultModel = 'claude-sonnet-4-6',
        private readonly string $apiVersion = '2023-06-01',
        private readonly int $timeoutSeconds = 60,
    ) {}

    public function complete(string $system, string $user, array $options = []): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Anthropic API key is not configured.');
        }

        // Pre-flight: refuse if a system cap is already exceeded. Cheap
        // (one indexed SUM query) and the exception is the public
        // contract callers handle as a soft failure.
        $this->tracker->guardSystemBudget();

        $payload = [
            'model' => $options['model'] ?? $this->defaultModel,
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $user],
            ],
        ];

        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }
        if (isset($options['stop_sequences'])) {
            $payload['stop_sequences'] = $options['stop_sequences'];
        }

        $response = Http::timeout($this->timeoutSeconds)
            ->withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => $this->apiVersion,
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', $payload);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Anthropic API error: HTTP '.$response->status().' — '.$response->body()
            );
        }

        $data = $response->json();

        // Cost the call against the operator ledger before returning.
        // Failure to record (eg. transient DB issue) should not break
        // the user-visible response — wrap to swallow.
        $context = (array) ($options['_context'] ?? []);
        try {
            $this->tracker->record(
                model: $payload['model'],
                inputTokens: (int) ($data['usage']['input_tokens'] ?? 0),
                outputTokens: (int) ($data['usage']['output_tokens'] ?? 0),
                feature: (string) ($context['feature'] ?? 'unknown'),
                teamId: $context['team_id'] ?? null,
                userId: $context['user_id'] ?? null,
            );
            $this->tracker->dispatchSoftAlertsIfNeeded($context['team_id'] ?? null);
        } catch (\Throwable $e) {
            report($e);
        }

        // Messages API returns content as an array of blocks; we want
        // the concatenated text from text-type blocks.
        $blocks = $data['content'] ?? [];
        $text = '';
        foreach ($blocks as $block) {
            if (($block['type'] ?? null) === 'text') {
                $text .= $block['text'] ?? '';
            }
        }

        return trim($text);
    }
}
