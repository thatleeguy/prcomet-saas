<?php

use App\Models\User;
use function Pest\Laravel\actingAs;

it('grants super-admins access to the Filament admin panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    actingAs($admin)
        ->get('/admin/teams')
        ->assertOk();
});

it('denies regular users access to the Filament admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    actingAs($user)
        ->get('/admin/teams')
        ->assertForbidden();
});

it('redirects unauthenticated users to the Filament login', function () {
    $this->get('/admin/teams')->assertRedirect('/admin/login');
});
