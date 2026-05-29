<?php

use App\Models\SystemHeartbeat;
use App\Models\User;
use App\Services\SystemHealthService;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('flags scheduler as failing when no heartbeat has been recorded', function () {
    $svc = app(SystemHealthService::class);
    expect($svc->checkScheduler()['status'])->toBe('fail');
});

it('flags scheduler as ok when a recent heartbeat exists', function () {
    SystemHeartbeat::beat('scheduler');
    expect(app(SystemHealthService::class)->checkScheduler()['status'])->toBe('ok');
});

it('flags scheduler as failing when the heartbeat is stale', function () {
    SystemHeartbeat::create([
        'kind' => 'scheduler',
        'last_at' => now()->subMinutes(10),
    ]);
    expect(app(SystemHealthService::class)->checkScheduler()['status'])->toBe('fail');
});

it('flags queue as failing when no heartbeat exists', function () {
    expect(app(SystemHealthService::class)->checkQueue()['status'])->toBe('fail');
});

it('flags queue as warn when fresh but pending depth is high', function () {
    // Switch off sync so the pending-job count actually consults the
    // jobs table (sync short-circuits to 0).
    config(['queue.default' => 'database']);

    SystemHeartbeat::beat('queue');

    if (! \Illuminate\Support\Facades\Schema::hasTable('jobs')) {
        \Illuminate\Support\Facades\Artisan::call('queue:table');
        \Illuminate\Support\Facades\Artisan::call('migrate');
    }
    for ($i = 0; $i < 100; $i++) {
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);
    }

    expect(app(SystemHealthService::class)->checkQueue()['status'])->toBe('warn');
});

it('reports the database connection as alive', function () {
    expect(app(SystemHealthService::class)->checkDatabase()['status'])->toBe('ok');
});

it('reports storage as ok after a write/read round-trip on the default disk', function () {
    config(['filesystems.default' => 'local']);
    expect(app(SystemHealthService::class)->checkStorage()['status'])->toBe('ok');
});

it('reports anthropic as warn when no API key is configured', function () {
    config(['services.anthropic.api_key' => '']);
    expect(app(SystemHealthService::class)->checkAnthropic()['status'])->toBe('warn');
});

it('reports anthropic as ok when an API key is configured', function () {
    config(['services.anthropic.api_key' => 'sk-test']);
    expect(app(SystemHealthService::class)->checkAnthropic()['status'])->toBe('ok');
});

it('renders the /manage system-health page for admins', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    SystemHeartbeat::beat('scheduler');
    SystemHeartbeat::beat('queue');

    actingAs($admin)
        ->get('/manage/system-health')
        ->assertOk()
        ->assertSee('Scheduler')
        ->assertSee('Queue worker');
});

it('denies non-admin users access to the system-health page', function () {
    $user = User::factory()->create(['is_admin' => false]);

    actingAs($user)
        ->get('/manage/system-health')
        ->assertForbidden();
});
