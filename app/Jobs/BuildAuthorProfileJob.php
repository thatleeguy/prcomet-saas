<?php

namespace App\Jobs;

use App\Models\Author;
use App\Services\Llm\LlmClient;
use App\Services\Llm\Prompts\AuthorProfilePrompt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

/**
 * Refresh an author's rolling body-of-work summary. Debounced via
 * WithoutOverlapping so multiple items from the same author in a short window
 * coalesce into a single profile update.
 */
class BuildAuthorProfileJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public int $authorId) {}

    public function middleware(): array
    {
        // 5-minute debounce window per author. Hot authors only get one
        // profile refresh per window even if 10 items land back-to-back.
        return [(new WithoutOverlapping((string) $this->authorId))->dontRelease()->expireAfter(300)];
    }

    public function handle(LlmClient $llm): void
    {
        $author = Author::find($this->authorId);
        if (! $author) {
            return;
        }

        $summary = AuthorProfilePrompt::run($llm, $author);

        if ($summary === '') {
            return;
        }

        $author->forceFill([
            'body_of_work_summary' => $summary,
            'body_of_work_updated_at' => now(),
        ])->save();
    }
}
