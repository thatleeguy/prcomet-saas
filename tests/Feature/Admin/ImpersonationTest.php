<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('returns the admin to their account when stopping impersonation', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $target = User::factory()->create();

    // Simulate the impersonation start: stash the admin's ID, log in as target.
    session()->put('impersonator_id', $admin->id);
    $this->actingAs($target);

    post(route('impersonate.stop'))
        ->assertRedirect('/admin');

    expect(auth()->id())->toBe($admin->id);
    expect(session('impersonator_id'))->toBeNull();
});

it('handles stopping impersonation when no session state exists', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('impersonate.stop'))
        ->assertRedirect('/');
});

it('logs out cleanly if the original admin no longer exists', function () {
    $target = User::factory()->create();

    session()->put('impersonator_id', 99999);
    $this->actingAs($target);

    post(route('impersonate.stop'))
        ->assertRedirect('/');

    expect(auth()->check())->toBeFalse();
});

it('shows the impersonation banner in the workspace layout when active', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $target = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);

    session()->put('impersonator_id', $admin->id);

    actingAs($target)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee("You're impersonating", false)
        ->assertSee($target->name)
        ->assertSee('Return to admin');
});

it('does not show the banner when not impersonating', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);

    actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee("You're impersonating", false);
});
