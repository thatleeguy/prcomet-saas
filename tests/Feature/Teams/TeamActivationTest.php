<?php

use App\Models\Team;
use App\Models\User;

it('activates a team with seats and records the admin', function () {
    $team = Team::factory()->create(['is_active' => false, 'max_companies' => 0]);
    $admin = User::factory()->create(['is_admin' => true]);

    $team->activate(seats: 5, admin: $admin);

    expect($team->fresh())
        ->is_active->toBeTrue()
        ->max_companies->toBe(5)
        ->activated_by_id->toBe($admin->id)
        ->activated_at->not->toBeNull();
});

it('preserves activated_at on re-activation but updates seats', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $team = Team::factory()->create();
    $team->activate(seats: 2, admin: $admin);
    $originalActivatedAt = $team->fresh()->activated_at;

    \Illuminate\Support\Carbon::setTestNow(now()->addDay());
    $team->activate(seats: 10, admin: $admin);
    \Illuminate\Support\Carbon::setTestNow();

    expect($team->fresh())
        ->max_companies->toBe(10)
        ->activated_at->format('Y-m-d H:i:s')->toBe($originalActivatedAt->format('Y-m-d H:i:s'));
});

it('suspends a team without deleting data', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $team = Team::factory()->create();
    $team->activate(seats: 3, admin: $admin);

    $team->suspend();

    expect($team->fresh())
        ->is_active->toBeFalse()
        ->max_companies->toBe(0);
});
