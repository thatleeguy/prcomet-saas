<?php

use App\Mail\TeamActivated;
use App\Mail\TeamSignedUp;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('emails super-admins when a new team signs up', function () {
    Mail::fake();

    $admin = User::factory()->create(['is_admin' => true, 'email' => 'admin@example.com']);
    $otherAdmin = User::factory()->create(['is_admin' => true, 'email' => 'admin2@example.com']);
    User::factory()->create(['is_admin' => false]); // should NOT receive

    $team = Team::factory()->create();

    Mail::assertQueued(TeamSignedUp::class, function (TeamSignedUp $mail) use ($admin, $otherAdmin, $team) {
        return $mail->team->is($team)
            && $mail->hasTo($admin->email)
            && $mail->hasTo($otherAdmin->email);
    });
});

it('does not send signup mail when there are no admins', function () {
    Mail::fake();

    Team::factory()->create();

    Mail::assertNothingQueued();
});

it('emails the team owner when their team is activated', function () {
    Mail::fake();

    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->withPersonalTeam()->create(['email' => 'owner@example.com']);
    $team = $owner->ownedTeams()->first();

    $team->activate(seats: 3, admin: $admin);

    Mail::assertQueued(TeamActivated::class, function (TeamActivated $mail) use ($team) {
        return $mail->team->is($team) && $mail->hasTo('owner@example.com');
    });
});

it('does not re-send activation mail on re-activation', function () {
    Mail::fake();

    $admin = User::factory()->create(['is_admin' => true]);
    $owner = User::factory()->withPersonalTeam()->create();
    $team = $owner->ownedTeams()->first();

    $team->activate(seats: 3, admin: $admin);
    $team->activate(seats: 10, admin: $admin); // change seats, not first activation

    Mail::assertQueued(TeamActivated::class, 1);
});
