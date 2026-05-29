<?php

namespace App\Services\Backup;

use RuntimeException;

/**
 * Picks the right BackupDriver for the current default database
 * connection. Keeps BackupService free of driver-detection logic —
 * adding Postgres later is a new case here, nothing else changes.
 */
class DriverFactory
{
    public function forDefaultConnection(): BackupDriver
    {
        $connection = (string) config('database.default');
        $config = config("database.connections.{$connection}");
        $driver = (string) ($config['driver'] ?? '');

        return match ($driver) {
            'sqlite' => new SqliteBackupDriver(
                databasePath: $config['database'],
            ),
            'mysql', 'mariadb' => new MysqlBackupDriver(
                host: (string) ($config['host'] ?? '127.0.0.1'),
                port: (int) ($config['port'] ?? 3306),
                database: (string) $config['database'],
                username: (string) $config['username'],
                password: (string) ($config['password'] ?? ''),
                mysqldumpBinary: config('backups.mysqldump_path'),
            ),
            default => throw new RuntimeException("No backup driver implemented for DB driver '{$driver}'."),
        };
    }
}
