<?php

namespace App\Services\Backup;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * MySQL dump via the mysqldump binary. Forge installs it by default
 * (and our docker baseline includes it), so we shell out rather than
 * reimplement schema-aware SQL generation in PHP.
 *
 * Credentials go in via a defaults-extra-file (a temp .cnf written
 * with 0600 perms) so they never appear on the process list or in
 * stderr. The .cnf is unlinked on completion in every code path.
 */
class MysqlBackupDriver implements BackupDriver
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $database,
        private readonly string $username,
        private readonly string $password,
        private readonly ?string $mysqldumpBinary = null,
    ) {}

    public function dumpTo(string $absolutePath): void
    {
        $binary = $this->resolveBinary();

        // Stash credentials in a 0600 file so they don't hit the
        // process list. The --defaults-extra-file flag MUST come
        // first or mysql will refuse to read it.
        $cnf = tempnam(sys_get_temp_dir(), 'mysqldump-');
        if ($cnf === false) {
            throw new RuntimeException('Failed to create temp credentials file for mysqldump.');
        }

        try {
            chmod($cnf, 0600);
            file_put_contents($cnf, sprintf(
                "[client]\nhost=%s\nport=%d\nuser=%s\npassword=\"%s\"\n",
                addslashes($this->host),
                $this->port,
                addslashes($this->username),
                addcslashes($this->password, "\"\\"),
            ));

            $process = new Process([
                $binary,
                '--defaults-extra-file='.$cnf,
                '--single-transaction',
                '--quick',
                '--no-tablespaces',
                '--routines',
                '--triggers',
                '--events',
                $this->database,
            ]);
            $process->setTimeout(60 * 30); // 30m for big DBs

            $handle = fopen($absolutePath, 'wb');
            if ($handle === false) {
                throw new RuntimeException("Cannot open {$absolutePath} for writing.");
            }

            try {
                $process->run(function ($type, $buffer) use ($handle) {
                    if ($type === Process::OUT) {
                        fwrite($handle, $buffer);
                    }
                    // Symfony writes mysqldump errors to ERR; we capture and
                    // surface them via the failure path below.
                });
            } finally {
                fclose($handle);
            }

            if (! $process->isSuccessful()) {
                @unlink($absolutePath);
                throw new RuntimeException(
                    'mysqldump failed (exit '.$process->getExitCode().'): '
                    .trim($process->getErrorOutput() ?: $process->getOutput())
                );
            }
        } finally {
            @unlink($cnf);
        }
    }

    public function name(): string
    {
        return 'mysql';
    }

    public function dumpExtension(): string
    {
        return 'sql';
    }

    private function resolveBinary(): string
    {
        if ($this->mysqldumpBinary && is_executable($this->mysqldumpBinary)) {
            return $this->mysqldumpBinary;
        }

        $path = trim((string) @shell_exec('which mysqldump'));
        if ($path === '' || ! is_executable($path)) {
            throw new RuntimeException('mysqldump binary not found on PATH. Set config(backups.mysqldump_path) or install mysql-client.');
        }
        return $path;
    }
}
