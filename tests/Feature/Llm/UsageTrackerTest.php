<?php

use App\Models\LlmUsageEvent;
use App\Models\Team;
use App\Models\User;
use App\Notifications\LlmBudgetAlertNotification;
use App\Services\Llm\BudgetExceededException;
use App\Services\Llm\LlmUsageTracker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Cache::flush(); // make sure soft-alert locks from a previous test don't bleed across
    config(['llm.budgets' => [
        'system_daily_cents' => 100,        // $1/day for tests
        'system_weekly_cents' => 500,       // $5/week for tests
        'team_daily_soft_cents' => 50,      // $0.50/team/day
        'system_approaching_pct' => 80,
    ]]);
    config(['llm.pricing.claude-sonnet-4-6' => [
        'input_cents_per_million' => 300,
        'output_cents_per_million' => 1500,
    ]]);
    config(['llm.alert_recipients' => []]);
});

it('blocks calls when the system daily cap is hit', function () {
    LlmUsageEvent::create([
        'team_id' => null, 'user_id' => null,
        'feature' => 'match_brief', 'model' => 'claude-sonnet-4-6',
        'input_tokens' => 0, 'output_tokens' => 0,
        'cost_cents' => 100, // == system daily cap
    ]);

    expect(fn () => app(LlmUsageTracker::class)->guardSystemBudget())
        ->toThrow(BudgetExceededException::class);
});

it('blocks calls when the system weekly cap is hit', function () {
    LlmUsageEvent::create([
        'team_id' => null, 'user_id' => null,
        'feature' => 'match_brief', 'model' => 'claude-sonnet-4-6',
        'input_tokens' => 0, 'output_tokens' => 0,
        'cost_cents' => 500, // == system weekly cap
    ]);

    expect(fn () => app(LlmUsageTracker::class)->guardSystemBudget())
        ->toThrow(BudgetExceededException::class);
});

it('allows calls when both caps have remaining headroom', function () {
    LlmUsageEvent::create([
        'team_id' => null, 'user_id' => null,
        'feature' => 'match_brief', 'model' => 'claude-sonnet-4-6',
        'input_tokens' => 0, 'output_tokens' => 0,
        'cost_cents' => 50,
    ]);

    app(LlmUsageTracker::class)->guardSystemBudget();
    expect(true)->toBeTrue(); // no throw
});

it('costs a call using the configured per-model rates and rounds up', function () {
    $tracker = app(LlmUsageTracker::class);
    // 1000 input tokens × 300c/1M = 0.3c, 1000 output × 1500c/1M = 1.5c → ceil(1.8) = 2c
    expect($tracker->costCentsFor('claude-sonnet-4-6', 1000, 1000))->toBe(2);
});

it('records an event with cost denormalised from pricing config', function () {
    $tracker = app(LlmUsageTracker::class);
    $event = $tracker->record(
        model: 'claude-sonnet-4-6',
        inputTokens: 1_000_000,
        outputTokens: 1_000_000,
        feature: 'match_brief',
        teamId: null,
        userId: null,
    );

    expect($event->cost_cents)->toBe(1800); // 300 + 1500 = 1800c
});

it('emails operators when a team crosses the daily soft cap, once per day', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_admin' => true]);
    $team = Team::factory()->create();

    $tracker = app(LlmUsageTracker::class);

    LlmUsageEvent::create([
        'team_id' => $team->id, 'feature' => 'watch_hit_confirm',
        'model' => 'claude-sonnet-4-6', 'input_tokens' => 0, 'output_tokens' => 0,
        'cost_cents' => 60, // above the $0.50 soft cap
    ]);

    $tracker->dispatchSoftAlertsIfNeeded($team->id);
    $tracker->dispatchSoftAlertsIfNeeded($team->id); // second call should be a no-op

    Notification::assertSentToTimes($admin, LlmBudgetAlertNotification::class, 1);
});

it('does not email when the team is below the soft cap', function () {
    Notification::fake();

    User::factory()->create(['is_admin' => true]);
    $team = Team::factory()->create();

    LlmUsageEvent::create([
        'team_id' => $team->id, 'feature' => 'watch_hit_confirm',
        'model' => 'claude-sonnet-4-6', 'input_tokens' => 0, 'output_tokens' => 0,
        'cost_cents' => 20,
    ]);

    app(LlmUsageTracker::class)->dispatchSoftAlertsIfNeeded($team->id);

    Notification::assertNothingSent();
});

it('emails operators when system spend crosses the approaching-cap threshold', function () {
    Notification::fake();

    $admin = User::factory()->create(['is_admin' => true]);

    LlmUsageEvent::create([
        'team_id' => null, 'feature' => 'match_brief',
        'model' => 'claude-sonnet-4-6', 'input_tokens' => 0, 'output_tokens' => 0,
        'cost_cents' => 90, // 90% of the $1 daily cap; >80% threshold
    ]);

    app(LlmUsageTracker::class)->dispatchSoftAlertsIfNeeded(null);

    Notification::assertSentTo($admin, LlmBudgetAlertNotification::class);
});

it('routes alerts to explicit recipients when configured', function () {
    Notification::fake();

    User::factory()->create(['is_admin' => true]); // ignored when explicit list is set
    config(['llm.alert_recipients' => ['ops@prcomet.com']]);

    LlmUsageEvent::create([
        'team_id' => null, 'feature' => 'match_brief',
        'model' => 'claude-sonnet-4-6', 'input_tokens' => 0, 'output_tokens' => 0,
        'cost_cents' => 90,
    ]);

    app(LlmUsageTracker::class)->dispatchSoftAlertsIfNeeded(null);

    Notification::assertSentOnDemand(LlmBudgetAlertNotification::class);
});
