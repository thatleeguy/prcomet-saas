<?php

namespace App\Listeners;

use App\Events\OnePagerPublished;
use App\Mail\InstantPublishNotice;
use App\Models\NewsroomSubscriber;
use Illuminate\Support\Facades\Mail;

/**
 * On publish, fan out a single InstantPublishNotice email to every
 * cadence=instant subscriber of the company. Daily/weekly cadence
 * subscribers are caught by the scheduled digest sweep instead, so
 * they get one digest at their preferred boundary rather than a
 * burst of individual emails.
 *
 * Listener itself runs synchronously; each Mail::queue call inside
 * pushes work onto the queue without holding the request thread.
 * Implementing ShouldQueue on the listener AND ShouldQueue-via-queue
 * on the mail was double-firing in the test harness — pick one
 * concurrency model, not both.
 */
class SendInstantPublishNotifications
{
    public function handle(OnePagerPublished $event): void
    {
        $page = $event->onePager;
        $page->loadMissing('company');

        $company = $page->company;
        if (! $company) {
            return;
        }

        // Cursor over the query so we don't materialise the whole
        // subscriber set into memory. chunkById was double-firing
        // the inner closure for the smaller test sets we hit during
        // demo runs — cursor is the simpler primitive.
        NewsroomSubscriber::query()
            ->where('cadence', NewsroomSubscriber::CADENCE_INSTANT)
            ->whereNotNull('confirmed_at')
            ->whereNull('unsubscribed_at')
            ->whereHas('subscriptions', fn ($q) => $q
                ->where('company_id', $company->id)
                ->whereNull('unsubscribed_at'))
            ->cursor()
            ->each(function (NewsroomSubscriber $subscriber) use ($company, $page) {
                Mail::to($subscriber->email)
                    ->queue(new InstantPublishNotice($subscriber, $company, $page));
            });
    }
}
