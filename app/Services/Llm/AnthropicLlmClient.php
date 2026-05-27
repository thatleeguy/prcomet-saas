<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Claude (Anthropic Messages API) implementation.
 *
 * Defaults to Sonnet for bulk extraction work; Opus is opt-in via $options['model']
 * for high-stakes paths (the match-brief generator in Phase 6).
 *
 * Configured via config/services.php → 'anthropic'.
 */
class AnthropicLlmClient implements LlmClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $defaultModel = 'claude-sonnet-4-6',
        private readonly string $apiVersion = '2023-06-01',
        private readonly int $timeoutSeconds = 60,
    ) {}

    public function complete(string $system, string $user, array $options = []): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Anthropic API key is not configured.');
        }

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

        // Messages API returns content as an array of blocks; we want the
        // concatenated text from text-type blocks.
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
