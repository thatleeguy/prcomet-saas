<?php

use App\Services\Backup\SqliteBackupDriver;

it('snapshots an existing sqlite database into the destination path', function () {
    $tempSource = tempnam(sys_get_temp_dir(), 'sqlite-source-').'.sqlite';
    $tempDest = tempnam(sys_get_temp_dir(), 'sqlite-dest-').'.sqlite';

    try {
        // Build a tiny SQLite file with one table + one row so we can
        // assert the snapshot is non-empty and the data round-trips.
        $pdo = new PDO('sqlite:'.$tempSource);
        $pdo->exec('CREATE TABLE smoke (id INTEGER PRIMARY KEY, name TEXT)');
        $pdo->exec("INSERT INTO smoke (name) VALUES ('hello')");
        unset($pdo);

        (new SqliteBackupDriver($tempSource))->dumpTo($tempDest);

        expect(file_exists($tempDest))->toBeTrue();
        expect(filesize($tempDest))->toBeGreaterThan(0);

        // Round-trip — the dump should be a usable SQLite db.
        $pdo = new PDO('sqlite:'.$tempDest);
        $row = $pdo->query('SELECT name FROM smoke LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        expect($row['name'])->toBe('hello');
    } finally {
        @unlink($tempSource);
        @unlink($tempDest);
    }
});

it('throws a clear error when the source database is missing', function () {
    $driver = new SqliteBackupDriver('/tmp/this-does-not-exist-'.uniqid().'.sqlite');

    expect(fn () => $driver->dumpTo('/tmp/dest.sqlite'))
        ->toThrow(\RuntimeException::class, 'SQLite database not found');
});

it('reports the driver name as sqlite and the dump extension as sqlite', function () {
    $driver = new SqliteBackupDriver('/tmp/whatever.sqlite');
    expect($driver->name())->toBe('sqlite');
    expect($driver->dumpExtension())->toBe('sqlite');
});
