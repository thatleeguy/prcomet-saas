<?php

namespace App\Listeners;

use App\Mail\TeamSignedUp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Events\TeamCreated;

/**
 * Email all super-admins when a new team signs up so they can activate it.
 *
 * Fires for every TeamCreated event — including the personal team auto-created
 * during user signup, which is exactly the moment we want to be notified.
 *
 * No-op if there are no super-admins yet (e.g. fresh dev environment).
 */
class NotifyAdminsOfTeamSignup
{
    public function handle(TeamCreated $event): void
    {
        $admins = User::query()->where('is_admin', true)->get();

        if ($admins->isEmpty()) {
            return;
        }

        Mail::to($admins->pluck('email')->all())
            ->send(new TeamSignedUp($event->team));
    }
}
