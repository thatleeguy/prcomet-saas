<?php

use App\Jobs\SendMatchDigestsJob;
use App\Mail\MatchDigest;
use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

it('emails a digest of new matches within the cadence window', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $user->update(['digest_frequency' => User::DIGEST_DAILY, 'digest_sent_at' => now()->subHours(25)]);

    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    MatchRecord::factory()->count(3)->create([
        'company_id' => $company->id,
        'created_at' => now()->subHours(2),
    ]);

    dispatch_sync(new SendMatchDigestsJob($user->id));

    Mail::assertQueued(MatchDigest::class, function (MatchDigest $m) use ($user) {
        return $m->hasTo($user->email) && $m->matches->count() === 3;
    });
    expect($user->fresh()->digest_sent_at)->not->toBeNull();
});

it('does not email when there are no new matches', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $user->update(['digest_frequency' => User::DIGEST_DAILY, 'digest_sent_at' => now()->subHours(25)]);

    dispatch_sync(new SendMatchDigestsJob($user->id));

    Mail::assertNothingQueued();
    // But still advances the window
    expect($user->fresh()->digest_sent_at->diffInMinutes(now()))->toBeLessThan(1);
});

it('skips users with digest_frequency off', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $user->update(['digest_frequency' => User::DIGEST_OFF]);

    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    MatchRecord::factory()->create(['company_id' => $company->id]);

    dispatch_sync(new SendMatchDigestsJob($user->id));

    Mail::assertNothingQueued();
});

it('skips users whose team is not active', function () {
    $user = makeUserWithTeam(); // inactive
    $user->update(['digest_frequency' => User::DIGEST_DAILY]);

    dispatch_sync(new SendMatchDigestsJob($user->id));

    Mail::assertNothingQueued();
});

it('saves the digest preference through the Livewire form', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 1]);

    \Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Settings\DigestPreferences::class)
        ->set('frequency', User::DIGEST_WEEKLY)
        ->call('save');

    expect($user->fresh()->digest_frequency)->toBe(User::DIGEST_WEEKLY);
});
