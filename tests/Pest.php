<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Build a User with a personal team and set current_team_id, so middleware
 * that reads $user->currentTeam works in tests.
 *
 * Pass $teamAttrs to override defaults (e.g. ['is_active' => true]).
 * Pass $userAttrs to override user fields (e.g. ['is_admin' => true]).
 */
function makeUserWithTeam(array $teamAttrs = [], array $userAttrs = []): \App\Models\User
{
    $user = \App\Models\User::factory()->withPersonalTeam()->create($userAttrs);
    $team = $user->ownedTeams()->first();

    if ($teamAttrs !== []) {
        $team->forceFill($teamAttrs)->save();
    }

    $user->forceFill(['current_team_id' => $team->id])->save();

    return $user->fresh();
}
