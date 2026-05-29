<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Illuminate\Console\Command;

/**
 * CLI hook for scheduled backups. Same code path as the operator UI
 * action — use this from cron or Forge's scheduler.
 *
 *   php artisan app:backup                  # full archive (DB + files)
 *   php artisan app:backup --db-only        # DB dump only
 *   php artisan app:backup --notes="nightly"
 */
class CreateBackup extends Command
{
    protected $signature = 'app:backup
        {--db-only : Skip the public uploads directory; produce a DB-only archive}
        {--notes= : Free-form note attached to the metadata row}';

    protected $description = 'Take a backup archive of the database (and optionally the public storage disk)';

    public function handle(BackupService $service): int
    {
        $this->info('Creating backup…');

        $backup = $service->create(
            createdByUserId: null,
            includeFiles: ! $this->option('db-only'),
            notes: $this->option('notes'),
        );

        $this->info("Backup saved: {$backup->filename} ({$backup->humanSize()})");

        return self::SUCCESS;
    }
}
