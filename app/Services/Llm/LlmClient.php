<?php

namespace App\Services\Llm;

/**
 * Provider-agnostic LLM contract.
 *
 * Implementations:
 * - {@see AnthropicLlmClient} — production, calls Claude via Messages API.
 * - {@see FakeLlmClient} — tests + dev when ANTHROPIC_API_KEY is unset.
 *
 * Kept deliberately narrow. If a prompt needs streaming, tool use, or images
 * down the line, add a sibling contract rather than widening this one.
 */
interface LlmClient
{
    /**
     * Send a single-turn prompt and return the assistant's text response.
     *
     * @param  string  $system     System prompt (instructions, persona, schema).
     * @param  string  $user       User message body.
     * @param  array{
     *     model?: string,
     *     max_tokens?: int,
     *     temperature?: float,
     *     stop_sequences?: array<int,string>,
     * }  $options
     */
    public function complete(string $system, string $user, array $options = []): string;
}
