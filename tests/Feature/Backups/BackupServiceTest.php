<?php

use App\Models\Backup;
use App\Services\Backup\BackupDriver;
use App\Services\Backup\BackupService;
use App\Services\Backup\DriverFactory;
use Illuminate\Support\Facades\Storage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Stage archives onto a disposable disk so the test never touches
    // real storage.
    config(['backups.disk' => 'backups-fake']);
    config(['filesystems.disks.backups-fake' => [
        'driver' => 'local',
        'root' => storage_path('framework/testing/backups-fake'),
    ]]);

    // Swap the real driver factory for one that returns a stub driver.
    // The service-level tests care about orchestration (zip layout,
    // manifest, metadata row, delete) — not driver internals. Driver
    // behaviour gets its own test below.
    $this->app->instance(DriverFactory::class, new class extends DriverFactory {
        public function forDefaultConnection(): BackupDriver
        {
            return new class implements BackupDriver {
                public function dumpTo(string $absolutePath): void
                {
                    file_put_contents($absolutePath, "-- fake dump --\n");
                }
                public function name(): string { return 'sqlite'; }
                public function dumpExtension(): string { return 'sqlite'; }
            };
        }
    });
});

afterEach(function () {
    @exec('rm -rf '.escapeshellarg(storage_path('framework/testing/backups-fake')));
    @exec('rm -rf '.escapeshellarg(storage_path('app/backups-staging')));
});

it('creates an archive on disk and records a metadata row', function () {
    $service = app(BackupService::class);

    $backup = $service->create(
        createdByUserId: null,
        includeFiles: false,
        notes: 'unit-test',
    );

    expect($backup)->toBeInstanceOf(Backup::class);
    expect($backup->disk)->toBe('backups-fake');
    expect($backup->db_driver)->toBe('sqlite');
    expect($backup->size_bytes)->toBeGreaterThan(0);
    expect($backup->notes)->toBe('unit-test');
    expect($backup->exists())->toBeTrue();
});

it('records includes_files=false for a DB-only backup', function () {
    $backup = app(BackupService::class)->create(includeFiles: false);
    expect($backup->includes_files)->toBeFalse();
});

it('records includes_files=true when uploads are bundled', function () {
    $backup = app(BackupService::class)->create(includeFiles: true);
    expect($backup->includes_files)->toBeTrue();
});

it('archive contains a manifest.json with driver + timestamp', function () {
    $backup = app(BackupService::class)->create(includeFiles: false);

    $archivePath = Storage::disk('backups-fake')->path($backup->filename);
    $zip = new ZipArchive;
    $zip->open($archivePath);
    $manifest = $zip->getFromName('manifest.json');
    $zip->close();

    expect($manifest)->not->toBeFalse();
    $data = json_decode($manifest, true);
    expect($data['db_driver'])->toBe('sqlite');
    expect($data['includes_files'])->toBeFalse();
    expect($data['created_at'])->toBeString();
});

it('archive contains the database dump file', function () {
    $backup = app(BackupService::class)->create(includeFiles: false);

    $archivePath = Storage::disk('backups-fake')->path($backup->filename);
    $zip = new ZipArchive;
    $zip->open($archivePath);
    $dumpIndex = $zip->locateName('database.sqlite');
    $zip->close();

    expect($dumpIndex)->not->toBeFalse();
});

it('delete() removes the archive from disk and the metadata row', function () {
    $service = app(BackupService::class);
    $backup = $service->create(includeFiles: false);
    $filename = $backup->filename;

    expect(Storage::disk('backups-fake')->exists($filename))->toBeTrue();

    $service->delete($backup);

    expect(Backup::find($backup->id))->toBeNull();
    expect(Storage::disk('backups-fake')->exists($filename))->toBeFalse();
});

it('cleans up the staging dir even if the dump step throws', function () {
    // Override the factory with one that always blows up so we can
    // verify the finally-block runs.
    $this->app->instance(DriverFactory::class, new class extends DriverFactory {
        public function forDefaultConnection(): BackupDriver
        {
            return new class implements BackupDriver {
                public function dumpTo(string $absolutePath): void
                {
                    throw new \RuntimeException('boom');
                }
                public function name(): string { return 'sqlite'; }
                public function dumpExtension(): string { return 'sqlite'; }
            };
        }
    });

    try {
        app(BackupService::class)->create();
    } catch (\RuntimeException) {
        // expected
    }

    expect(Backup::count())->toBe(0);
    $staging = storage_path('app/backups-staging');
    $left = is_dir($staging)
        ? array_filter(scandir($staging), fn ($n) => ! in_array($n, ['.', '..']))
        : [];
    expect($left)->toBe([]);
});
