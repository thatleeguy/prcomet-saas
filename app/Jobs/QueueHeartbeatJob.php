<?php

namespace App\Jobs;

use App\Models\SystemHeartbeat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Single-line "the queue is processing" pingback.
 *
 * Dispatched by the scheduler every minute. If the queue worker is
 * dead, this job piles up in the `jobs` table and the SystemHeartbeat
 * row for `queue` goes stale — which the System health page reads as
 * a red light. Cheap (under a millisecond) so dispatching often
 * doesn't move the spend needle.
 */
class QueueHeartbeatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        SystemHeartbeat::beat(SystemHeartbeat::KIND_QUEUE, [
            'host' => gethostname() ?: null,
            'connection' => config('queue.default'),
        ]);
    }
}
