<?php

namespace App\Services\Llm;

/**
 * In-memory LLM client for tests and dev environments without an API key.
 *
 * Programmable in two ways:
 * - {@see queue} pushes a fixed response onto a FIFO queue.
 * - {@see callback} sets a closure called with ($system, $user, $options) to
 *   compute a response on the fly.
 *
 * If both are set, the queue takes precedence. If neither, returns an empty
 * JSON object so JSON-decoding callers don't blow up.
 */
class FakeLlmClient implements LlmClient
{
    /** @var array<int,string> */
    private array $queue = [];

    /** @var \Closure|null */
    private $callback = null;

    /** @var array<int, array{system:string, user:string, options:array}> */
    public array $calls = [];

    public function queue(string $response): self
    {
        $this->queue[] = $response;
        return $this;
    }

    public function callback(\Closure $cb): self
    {
        $this->callback = $cb;
        return $this;
    }

    public function complete(string $system, string $user, array $options = []): string
    {
        $this->calls[] = ['system' => $system, 'user' => $user, 'options' => $options];

        if ($this->queue !== []) {
            return array_shift($this->queue);
        }

        if ($this->callback !== null) {
            return ($this->callback)($system, $user, $options);
        }

        return '{}';
    }
}
