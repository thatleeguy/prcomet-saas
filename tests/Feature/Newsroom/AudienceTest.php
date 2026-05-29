<?php

use App\Livewire\Companies\Audience;
use App\Models\Company;
use App\Models\NewsroomSubscriber;
use App\Models\NewsroomSubscription;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows aggregate stats partitioned by cadence and confirmation', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    $confirmedWeekly = NewsroomSubscriber::create([
        'email' => 'a@example.com', 'cadence' => 'weekly', 'confirmed_at' => now()->subDays(3),
    ]);
    $confirmedDaily = NewsroomSubscriber::create([
        'email' => 'b@example.com', 'cadence' => 'daily', 'confirmed_at' => now()->subDays(3),
    ]);
    $confirmedInstant = NewsroomSubscriber::create([
        'email' => 'c@example.com', 'cadence' => 'instant', 'confirmed_at' => now()->subDays(3),
    ]);
    $pending = NewsroomSubscriber::create([
        'email' => 'p@example.com', 'cadence' => 'weekly',
    ]);

    foreach ([$confirmedWeekly, $confirmedDaily, $confirmedInstant, $pending] as $s) {
        NewsroomSubscription::create([
            'newsroom_subscriber_id' => $s->id, 'company_id' => $company->id, 'subscribed_at' => now(),
        ]);
    }

    $component = Livewire::actingAs($user)->test(Audience::class, ['company' => $company]);
    $stats = $component->instance()->stats;

    expect($stats['total_active'])->toBe(3);
    expect($stats['unconfirmed'])->toBe(1);
    expect($stats['weekly'])->toBe(1);
    expect($stats['daily'])->toBe(1);
    expect($stats['instant'])->toBe(1);
});

it('forbids access to another team\'s company audience', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $foreign = Company::factory()->create();

    actingAs($user)
        ->get(route('companies.audience', $foreign))
        ->assertForbidden();
});

it('renders the audience page for the team owner', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id, 'newsroom_published' => true]);

    actingAs($user)
        ->get(route('companies.audience', $company))
        ->assertOk()
        ->assertSee('Newsroom audience');
});

it('filters subscribers by cadence', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    $weekly = NewsroomSubscriber::create([
        'email' => 'w@example.com', 'name' => 'Weekly Watcher',
        'cadence' => 'weekly', 'confirmed_at' => now()->subDay(),
    ]);
    $daily = NewsroomSubscriber::create([
        'email' => 'd@example.com', 'name' => 'Daily Watcher',
        'cadence' => 'daily', 'confirmed_at' => now()->subDay(),
    ]);

    NewsroomSubscription::create(['newsroom_subscriber_id' => $weekly->id, 'company_id' => $company->id, 'subscribed_at' => now()]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $daily->id, 'company_id' => $company->id, 'subscribed_at' => now()]);

    Livewire::actingAs($user)->test(Audience::class, ['company' => $company])
        ->call('setFilter', 'weekly')
        ->assertSee('Weekly Watcher')
        ->assertDontSee('Daily Watcher');
});

it('searches by email or name', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    $rob = NewsroomSubscriber::create([
        'email' => 'rob@example.com', 'name' => 'Rob Sinclair',
        'cadence' => 'weekly', 'confirmed_at' => now()->subDay(),
    ]);
    $maria = NewsroomSubscriber::create([
        'email' => 'maria@example.com', 'name' => 'Maria Cardoso',
        'cadence' => 'weekly', 'confirmed_at' => now()->subDay(),
    ]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $rob->id, 'company_id' => $company->id, 'subscribed_at' => now()]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $maria->id, 'company_id' => $company->id, 'subscribed_at' => now()]);

    Livewire::actingAs($user)->test(Audience::class, ['company' => $company])
        ->set('search', 'sinclair')
        ->assertSee('Rob Sinclair')
        ->assertDontSee('Maria Cardoso');
});

it('exports a CSV of active subscribers', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    $sub = NewsroomSubscriber::create([
        'email' => 'rob@example.com', 'name' => 'Rob Sinclair',
        'cadence' => 'weekly', 'confirmed_at' => now()->subDay(),
    ]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $sub->id, 'company_id' => $company->id, 'subscribed_at' => now()]);

    // Call the export directly on a freshly-mounted component
    // instance — the Livewire test harness wraps the streamed
    // response as a TestResponse, which obscures the body. Sign in
    // BEFORE mount() because the mount enforces a team-active guard.
    actingAs($user);
    $component = new Audience;
    $component->mount($company);

    $response = $component->exportCsv();

    ob_start();
    $response->sendContent();
    $body = ob_get_clean();

    expect($body)->toContain('rob@example.com');
    expect($body)->toContain('Rob Sinclair');
    expect($body)->toContain('Weekly digest');
});

it('excludes globally-unsubscribed identities from the audience list', function () {
    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);

    $active = NewsroomSubscriber::create([
        'email' => 'a@example.com', 'cadence' => 'weekly', 'confirmed_at' => now()->subDay(),
    ]);
    $globalUnsub = NewsroomSubscriber::create([
        'email' => 'u@example.com', 'cadence' => 'weekly', 'confirmed_at' => now()->subDay(),
        'unsubscribed_at' => now()->subHour(),
    ]);

    NewsroomSubscription::create(['newsroom_subscriber_id' => $active->id, 'company_id' => $company->id, 'subscribed_at' => now()]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $globalUnsub->id, 'company_id' => $company->id, 'subscribed_at' => now()]);

    $component = Livewire::actingAs($user)->test(Audience::class, ['company' => $company]);

    expect($component->instance()->stats['total_active'])->toBe(1);
});
