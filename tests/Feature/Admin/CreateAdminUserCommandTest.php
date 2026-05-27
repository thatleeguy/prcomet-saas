<?php

use App\Models\User;
use function Pest\Laravel\artisan;

it('promotes an existing user to super-admin', function () {
    $user = User::factory()->create(['email' => 'lee@example.com', 'is_admin' => false]);

    artisan('app:create-admin', ['email' => 'lee@example.com'])
        ->assertSuccessful();

    expect($user->fresh()->is_admin)->toBeTrue();
});

it('creates a new admin user when --password and --name are passed', function () {
    artisan('app:create-admin', [
        'email' => 'new@example.com',
        '--password' => 'hunter22',
        '--name' => 'New Admin',
    ])->assertSuccessful();

    $user = User::where('email', 'new@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_admin)->toBeTrue()
        ->and($user->name)->toBe('New Admin')
        ->and($user->email_verified_at)->not->toBeNull();
});

it('fails when user does not exist and no password is provided', function () {
    artisan('app:create-admin', ['email' => 'missing@example.com'])
        ->assertFailed();

    expect(User::where('email', 'missing@example.com')->exists())->toBeFalse();
});

it('rejects invalid email arguments', function () {
    artisan('app:create-admin', ['email' => 'not-an-email'])
        ->assertFailed();
});
