<?php

namespace App\Services;

use App\Models\SystemHeartbeat;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Computes the green/amber/red checks the operator sees on /manage
 * System health. Pure reads — never modifies state, so the page can
 * refresh as often as the operator wants without side effects.
 *
 * Status conventions:
 *   - 'ok'      — green; behaviour matches expectations.
 *   - 'warn'    — amber; degraded but not failed (eg. a queue with
 *                 200 pending jobs still being drained).
 *   - 'fail'    — red; heartbeat stale, FS unreachable, etc.
 *   - 'unknown' — grey; can't tell (eg. queue table missing).
 */
class SystemHealthService
{
    /** Scheduler beat must be no older than this to be green. */
    private const SCHEDULER_FRESH_SECONDS = 180; // 3 minutes

    /** Queue beat must be no older than this to be green. */
    private const QUEUE_FRESH_SECONDS = 180;

    /**
     * @return array{
     *   scheduler: array{status:string, last_at: ?CarbonInterface, message:string},
     *   queue:     array{status:string, last_at: ?CarbonInterface, message:string, pending:int, failed:int},
     *   database:  array{status:string, message:string},
     *   storage:   array{status:string, message:string, disk:string},
     *   anthropic: array{status:string, message:string},
     * }
     */
    public function checkAll(): array
    {
        return [
            'scheduler' => $this->checkScheduler(),
            'queue' => $this->checkQueue(),
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'anthropic' => $this->checkAnthropic(),
        ];
    }

    public function checkScheduler(): array
    {
        $last = SystemHeartbeat::last(SystemHeartbeat::KIND_SCHEDULER);

        if (! $last) {
            return [
                'status' => 'fail',
                'last_at' => null,
                'message' => 'No scheduler beat recorded. Confirm cron is calling `php artisan schedule:run`.',
            ];
        }

        $age = $last->diffInSeconds(now());
        return [
            'status' => $age <= self::SCHEDULER_FRESH_SECONDS ? 'ok' : 'fail',
            'last_at' => $last,
            'message' => $age <= self::SCHEDULER_FRESH_SECONDS
                ? "Last beat {$last->diffForHumans()}."
                : "Last beat {$last->diffForHumans()} — older than the 3-minute freshness window. Check Forge's scheduler cron.",
        ];
    }

    public function checkQueue(): array
    {
        $last = SystemHeartbeat::last(SystemHeartbeat::KIND_QUEUE);

        $pending = $this->countPendingJobs();
        $failed = $this->countFailedJobs();

        if (! $last) {
            return [
                'status' => 'fail',
                'last_at' => null,
                'message' => 'No queue beat recorded. Confirm the queue worker daemon is running on Forge.',
                'pending' => $pending,
                'failed' => $failed,
            ];
        }

        $age = $last->diffInSeconds(now());
        $fresh = $age <= self::QUEUE_FRESH_SECONDS;

        // Queue is "warn" when fresh but jobs are backing up — worker
        // alive but falling behind.
        $status = match (true) {
            ! $fresh => 'fail',
            $pending >= 100 => 'warn',
            default => 'ok',
        };

        $message = $fresh
            ? "Last beat {$last->diffForHumans()}."
            : "Last beat {$last->diffForHumans()} — older than 3 minutes. Restart the worker daemon.";

        if ($status === 'warn') {
            $message .= " Pending queue depth ({$pending}) is high — worker may be falling behind.";
        }

        return [
            'status' => $status,
            'last_at' => $last,
            'message' => $message,
            'pending' => $pending,
            'failed' => $failed,
        ];
    }

    public function checkDatabase(): array
    {
        try {
            DB::connection()->select('select 1');
            return [
                'status' => 'ok',
                'message' => 'Connection alive ('.config('database.default').').',
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'message' => 'DB connection failed: '.$e->getMessage(),
            ];
        }
    }

    public function checkStorage(): array
    {
        $disk = (string) config('filesystems.default');

        try {
            $probe = 'health/probe-'.now()->getTimestampMs();
            Storage::disk($disk)->put($probe, 'ok');
            $payload = Storage::disk($disk)->get($probe);
            Storage::disk($disk)->delete($probe);

            return [
                'status' => $payload === 'ok' ? 'ok' : 'warn',
                'message' => $payload === 'ok'
                    ? 'Read/write round-trip succeeded.'
                    : 'Wrote but read back unexpected payload.',
                'disk' => $disk,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'message' => 'Storage probe failed: '.$e->getMessage(),
                'disk' => $disk,
            ];
        }
    }

    public function checkAnthropic(): array
    {
        $apiKey = (string) config('services.anthropic.api_key', '');
        if ($apiKey === '') {
            return [
                'status' => 'warn',
                'message' => 'ANTHROPIC_API_KEY not set — LLM features fall back to the fake client.',
            ];
        }
        return [
            'status' => 'ok',
            'message' => 'API key configured. (Live ping is not run from this page to keep page loads cheap.)',
        ];
    }

    public function countPendingJobs(): int
    {
        if (config('queue.default') === 'sync') {
            return 0; // sync jobs run inline, never pending.
        }
        if (! Schema::hasTable('jobs')) {
            return 0;
        }
        return (int) DB::table('jobs')->count();
    }

    public function countFailedJobs(): int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return 0;
        }
        return (int) DB::table('failed_jobs')->count();
    }

    /**
     * Last 10 failed jobs (most-recent first) for the operator panel.
     * @return array<int, array{id:int|string, queue:string, exception:string, failed_at: ?CarbonInterface}>
     */
    public function recentFailures(int $limit = 10): array
    {
        if (! Schema::hasTable('failed_jobs')) {
            return [];
        }

        return collect(DB::table('failed_jobs')->orderByDesc('failed_at')->limit($limit)->get())
            ->map(fn ($row) => [
                'id' => $row->uuid ?? $row->id,
                'queue' => $row->queue,
                // Trim the stack trace down to the first line.
                'exception' => strtok((string) $row->exception, "\n"),
                'failed_at' => isset($row->failed_at) ? Carbon::parse($row->failed_at) : null,
            ])
            ->all();
    }
}
