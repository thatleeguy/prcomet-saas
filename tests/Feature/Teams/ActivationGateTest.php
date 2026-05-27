<?php

use function Pest\Laravel\actingAs;

it('redirects inactive-team users from the workspace to the pending page', function () {
    $user = makeUserWithTeam(); // is_active defaults to false

    actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('teams.pending'));
});

it('allows active-team users into the workspace', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);

    actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('lets super-admins bypass the activation gate', function () {
    $user = makeUserWithTeam([], ['is_admin' => true]);

    actingAs($user)
        ->get('/dashboard')
        ->assertOk();
});

it('shows the pending-activation page to inactive-team users', function () {
    $user = makeUserWithTeam();

    actingAs($user)
        ->get(route('teams.pending'))
        ->assertOk()
        ->assertSee('pending activation');
});

it('redirects unauthenticated users from the workspace to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});
