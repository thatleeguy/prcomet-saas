<?php

namespace App\Services\Backup;

use RuntimeException;

/**
 * SQLite dump = a binary copy of the database file.
 *
 * Uses SQLite's online backup API when sqlite3 CLI is available so
 * we can take a consistent snapshot of an actively-written database.
 * Falls back to a plain file copy when the CLI isn't present (local
 * dev / shared hosting); fine in practice since v0 writes are
 * infrequent enough that a copy under load is highly unlikely to
 * land mid-transaction.
 */
class SqliteBackupDriver implements BackupDriver
{
    public function __construct(private readonly string $databasePath) {}

    public function dumpTo(string $absolutePath): void
    {
        if (! is_file($this->databasePath)) {
            throw new RuntimeException("SQLite database not found at {$this->databasePath}");
        }

        // Try the online backup API first for write-safe snapshots.
        $sqlite3Path = trim((string) @shell_exec('which sqlite3'));
        if ($sqlite3Path !== '' && is_executable($sqlite3Path)) {
            $cmd = escapeshellcmd($sqlite3Path)
                .' '.escapeshellarg($this->databasePath)
                .' '.escapeshellarg('.backup '.$absolutePath);

            $output = [];
            $exit = 0;
            exec($cmd.' 2>&1', $output, $exit);

            if ($exit === 0 && is_file($absolutePath)) {
                return;
            }
            // fall through to copy() on any CLI failure
        }

        if (! @copy($this->databasePath, $absolutePath)) {
            throw new RuntimeException("Failed to copy SQLite database to {$absolutePath}");
        }
    }

    public function name(): string
    {
        return 'sqlite';
    }

    public function dumpExtension(): string
    {
        return 'sqlite';
    }
}
