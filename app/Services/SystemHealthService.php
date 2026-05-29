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
 *
 * Each check carries a `setup` block — the operator-facing
 * configuration recipe (env vars + shell commands + Forge notes)
 * for that subsystem. The view shows it open-by-default when status
 * is not ok so the fix is one glance away from the failure.
 */
class SystemHealthService
{
    /** Scheduler beat must be no older than this to be green. */
    private const SCHEDULER_FRESH_SECONDS = 180; // 3 minutes

    /** Queue beat must be no older than this to be green. */
    private const QUEUE_FRESH_SECONDS = 180;

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

        $setup = [
            'env' => [],
            'commands' => [
                '# Forge → Site → Settings → Scheduler: toggle ON.',
                '# Or, on a raw server, add to the deploy user\'s crontab via `crontab -e`:',
                "* * * * * cd /home/forge/{$this->siteRoot()} && php artisan schedule:run >> /dev/null 2>&1",
            ],
            'forge_notes' => 'Forge: the "Scheduler" toggle on Site → Settings adds this entry to the forge user\'s crontab automatically. Confirm it\'s ON.',
        ];

        if (! $last) {
            return [
                'status' => 'fail',
                'last_at' => null,
                'message' => 'No scheduler beat recorded. Confirm cron is calling `php artisan schedule:run`.',
                'setup' => $setup,
            ];
        }

        $age = $last->diffInSeconds(now());
        return [
            'status' => $age <= self::SCHEDULER_FRESH_SECONDS ? 'ok' : 'fail',
            'last_at' => $last,
            'message' => $age <= self::SCHEDULER_FRESH_SECONDS
                ? "Last beat {$last->diffForHumans()}."
                : "Last beat {$last->diffForHumans()} — older than the 3-minute freshness window. Check Forge's scheduler cron.",
            'setup' => $setup,
        ];
    }

    public function checkQueue(): array
    {
        $last = SystemHeartbeat::last(SystemHeartbeat::KIND_QUEUE);

        $pending = $this->countPendingJobs();
        $failed = $this->countFailedJobs();

        $setup = [
            'env' => [
                'QUEUE_CONNECTION=database  # or redis on production',
            ],
            'commands' => [
                '# Forge → Site → Daemons → "New Daemon":',
                'Command: php artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600',
                "User: forge",
                "Directory: /home/forge/{$this->siteRoot()}",
                '',
                '# Local (foreground for dev):',
                'php artisan queue:work --queue=default',
                '',
                '# Restart workers after a deploy (Forge does this automatically):',
                'php artisan queue:restart',
            ],
            'forge_notes' => 'Forge: Daemons tab → New Daemon → use the queue:work command above. Forge supervises it; check the Daemons tab if it\'s flapping.',
        ];

        if (! $last) {
            return [
                'status' => 'fail',
                'last_at' => null,
                'message' => 'No queue beat recorded. Confirm the queue worker daemon is running on Forge.',
                'pending' => $pending,
                'failed' => $failed,
                'setup' => $setup,
            ];
        }

        $age = $last->diffInSeconds(now());
        $fresh = $age <= self::QUEUE_FRESH_SECONDS;

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
            'setup' => $setup,
        ];
    }

    public function checkDatabase(): array
    {
        $driver = (string) config('database.default');

        $setup = [
            'env' => $driver === 'mysql' ? [
                'DB_CONNECTION=mysql',
                'DB_HOST=127.0.0.1',
                'DB_PORT=3306',
                'DB_DATABASE=prcomet_saas',
                'DB_USERNAME=forge',
                'DB_PASSWORD=<set on Forge → Site → Environment>',
            ] : [
                'DB_CONNECTION=sqlite',
                'DB_DATABASE=/absolute/path/to/database.sqlite',
            ],
            'commands' => [
                '# Apply migrations after env is set:',
                'php artisan migrate --force',
                '',
                '# Verify the connection from the shell:',
                'php artisan db:show',
            ],
            'forge_notes' => 'Forge: Database tab manages MySQL/Postgres credentials; copy them into Site → Environment. For SQLite (local only), just point DB_DATABASE at the file path.',
        ];

        try {
            DB::connection()->select('select 1');
            return [
                'status' => 'ok',
                'message' => "Connection alive ({$driver}).",
                'setup' => $setup,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'message' => 'DB connection failed: '.$e->getMessage(),
                'setup' => $setup,
            ];
        }
    }

    public function checkStorage(): array
    {
        $disk = (string) config('filesystems.default');

        $setup = [
            'env' => $disk === 's3' ? [
                'FILESYSTEM_DISK=s3',
                'AWS_ACCESS_KEY_ID=<set on Forge → Site → Environment>',
                'AWS_SECRET_ACCESS_KEY=<set on Forge → Site → Environment>',
                'AWS_DEFAULT_REGION=us-east-1',
                'AWS_BUCKET=prcomet-uploads',
                'AWS_URL=https://<bucket>.s3.<region>.amazonaws.com',
            ] : [
                'FILESYSTEM_DISK=public  # or local / s3',
            ],
            'commands' => [
                '# For the public disk (default in dev), symlink so uploaded files are web-accessible:',
                'php artisan storage:link',
                '',
                '# Verify config + writability:',
                'php artisan about --only=drivers',
            ],
            'forge_notes' => 'Forge: set FILESYSTEM_DISK=s3 in Site → Environment for production. The page probes by writing a tiny health/probe-* file and reading it back, so any IAM mis-config surfaces here within seconds.',
        ];

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
                'setup' => $setup,
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'fail',
                'message' => 'Storage probe failed: '.$e->getMessage(),
                'disk' => $disk,
                'setup' => $setup,
            ];
        }
    }

    public function checkAnthropic(): array
    {
        $apiKey = (string) config('services.anthropic.api_key', '');

        $setup = [
            'env' => [
                'ANTHROPIC_API_KEY=sk-ant-...   # get it from https://console.anthropic.com/settings/keys',
                'ANTHROPIC_DEFAULT_MODEL=claude-sonnet-4-6',
                'ANTHROPIC_HIGH_STAKES_MODEL=claude-opus-4-7',
            ],
            'commands' => [
                '# After setting the key in Forge → Site → Environment, restart workers',
                '# so cached config is reloaded:',
                'php artisan config:clear',
                'php artisan queue:restart',
                '',
                '# Tail one Claude call end-to-end to sanity-check creds:',
                'php artisan tinker',
                '>>> app(\App\Services\Llm\LlmClient::class)->complete("you are helpful", "say hi")',
            ],
            'forge_notes' => 'Forge: Site → Environment → add ANTHROPIC_API_KEY. The page only checks that the env var is present; spend lives on the main /manage dashboard widget.',
        ];

        if ($apiKey === '') {
            return [
                'status' => 'warn',
                'message' => 'ANTHROPIC_API_KEY not set — LLM features fall back to the fake client.',
                'setup' => $setup,
            ];
        }
        return [
            'status' => 'ok',
            'message' => 'API key configured. (Live ping is not run from this page to keep page loads cheap.)',
            'setup' => $setup,
        ];
    }

    public function countPendingJobs(): int
    {
        if (config('queue.default') === 'sync') {
            return 0;
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
                'exception' => strtok((string) $row->exception, "\n"),
                'failed_at' => isset($row->failed_at) ? Carbon::parse($row->failed_at) : null,
            ])
            ->all();
    }

    /**
     * Best-effort guess at the deploy path's basename so the setup
     * commands read naturally on the operator's terminal. Falls back
     * to a generic placeholder when we can't tell.
     */
    private function siteRoot(): string
    {
        $url = (string) config('app.url');
        $host = parse_url($url, PHP_URL_HOST);
        return $host ?: 'your-site.com';
    }
}
