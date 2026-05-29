<?php

namespace App\Services\Backup;

use App\Models\Backup;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

/**
 * Orchestrates a single backup run:
 *   1. Resolve the driver for the configured DB connection.
 *   2. Stage the DB dump (and optionally the public files) into a
 *      temp working dir.
 *   3. Zip the staging dir into a single archive.
 *   4. Move the archive onto the configured backup disk.
 *   5. Record metadata in the backups table.
 *
 * The temp dir is always wiped, even on failure, so a half-finished
 * dump doesn't fill the filesystem on repeated retries.
 */
class BackupService
{
    public function __construct(
        private readonly DriverFactory $driverFactory,
    ) {}

    /**
     * Run a backup. Returns the persisted Backup row.
     */
    public function create(?int $createdByUserId = null, bool $includeFiles = true, ?string $notes = null): Backup
    {
        $driver = $this->driverFactory->forDefaultConnection();
        $diskName = (string) config('backups.disk', 'local');
        $disk = Storage::disk($diskName);

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $stem = "backup-{$timestamp}";
        $stagingRoot = storage_path('app/backups-staging');
        $stagingDir = $stagingRoot.'/'.$stem;
        $archivePath = $stagingRoot.'/'.$stem.'.zip';

        try {
            @mkdir($stagingDir, 0775, true);

            // 1. Database dump.
            $dumpFile = $stagingDir.'/database.'.$driver->dumpExtension();
            $driver->dumpTo($dumpFile);

            // 2. Manifest — operator-readable receipt of what's in the archive.
            file_put_contents($stagingDir.'/manifest.json', json_encode([
                'created_at' => Carbon::now()->toIso8601String(),
                'db_driver' => $driver->name(),
                'includes_files' => $includeFiles,
                'app_url' => (string) config('app.url'),
                'app_version' => (string) config('app.version', 'dev'),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // 3. Optionally bundle the public upload disk.
            if ($includeFiles) {
                $this->copyPublicFiles($stagingDir.'/public');
            }

            // 4. Zip everything.
            $this->zipDirectory($stagingDir, $archivePath);

            // 5. Move onto the backup disk and record metadata.
            $relative = 'backups/'.$stem.'.zip';
            $disk->put($relative, fopen($archivePath, 'rb'));

            $backup = Backup::create([
                'filename' => $relative,
                'disk' => $diskName,
                'db_driver' => $driver->name(),
                'includes_files' => $includeFiles,
                'size_bytes' => $disk->size($relative),
                'created_by_user_id' => $createdByUserId,
                'notes' => $notes,
            ]);

            return $backup;
        } finally {
            // Always tidy the staging dir, even if anything above blew up.
            $this->removeDir($stagingDir);
            if (is_file($archivePath)) {
                @unlink($archivePath);
            }
        }
    }

    /**
     * Delete the underlying archive and its metadata row.
     */
    public function delete(Backup $backup): void
    {
        if ($backup->exists()) {
            $backup->disk()->delete($backup->filename);
        }
        $backup->delete();
    }

    private function copyPublicFiles(string $destination): void
    {
        $source = storage_path('app/public');
        if (! is_dir($source)) {
            return;
        }

        @mkdir($destination, 0775, true);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($source) + 1);
            $target = $destination.'/'.$relative;
            if ($item->isDir()) {
                @mkdir($target, 0775, true);
            } else {
                @copy($item->getPathname(), $target);
            }
        }
    }

    private function zipDirectory(string $sourceDir, string $archivePath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Cannot create archive at {$archivePath}");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = substr($item->getPathname(), strlen($sourceDir) + 1);
            if ($item->isDir()) {
                $zip->addEmptyDir($relative);
            } else {
                $zip->addFile($item->getPathname(), $relative);
            }
        }

        $zip->close();
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($dir);
    }
}
