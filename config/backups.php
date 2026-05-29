<?php

/**
 * Backup tool configuration. Operator-only — never surfaced to
 * customers.
 */
return [
    // Disk to store archives on. Default 'local' (filesystem). For
    // Forge / production override to 's3' (or 's3-backups') so
    // archives live off the web server.
    'disk' => env('BACKUP_DISK', 'local'),

    // Override mysqldump location if the default `which mysqldump`
    // lookup misses on a non-standard install.
    'mysqldump_path' => env('BACKUP_MYSQLDUMP_PATH'),

    // Maximum age before backups become eligible for the prune job.
    // Manual deletes happen via the UI; this is for an optional cron.
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
];
