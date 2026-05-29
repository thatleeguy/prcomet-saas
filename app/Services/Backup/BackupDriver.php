<?php

namespace App\Services\Backup;

/**
 * Strategy contract for getting a single-file database dump on disk.
 *
 * Implementations target one DB driver each. The BackupService picks
 * the right one at runtime based on config('database.default'), so
 * adding Postgres later is a new class + a switch arm, not a refactor.
 */
interface BackupDriver
{
    /**
     * Write a database dump to the given absolute filesystem path.
     * Throws on failure; never returns silently.
     */
    public function dumpTo(string $absolutePath): void;

    /**
     * Short label for the dump output ("sqlite", "mysql", etc) — used
     * in the manifest + the operator-visible "DB driver" column.
     */
    public function name(): string;

    /**
     * Filename extension for the dump inside the archive ("sqlite" for
     * a copied SQLite db, "sql" for a mysqldump). Used when naming
     * the inner file.
     */
    public function dumpExtension(): string;
}
