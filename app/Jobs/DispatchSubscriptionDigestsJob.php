<?php

namespace App\Jobs;

use App\Mail\SubscriptionDigest;
use App\Models\NewsroomSubscriber;
use App\Models\OnePager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Periodic sweep that builds and mails the digest to every daily /
 * weekly subscriber who is due.
 *
 * "Due" means now() ≥ last_digest_sent_at + cadence_interval
 * (or last_digest_sent_at is null and the subscriber was created
 * more than cadence_interval ago).
 *
 * Builds the digest only when at least one company has a publish
 * the subscriber hasn't seen — empty cycles are skipped silently so
 * inboxes stay quiet on slow weeks.
 *
 * Stamps last_digest_sent_at on send so the next sweep starts from
 * the right baseline.
 */
class DispatchSubscriptionDigestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = now();

        NewsroomSubscriber::query()
            ->whereIn('cadence', [
                NewsroomSubscriber::CADENCE_DAILY,
                NewsroomSubscriber::CADENCE_WEEKLY,
            ])
            ->whereNotNull('confirmed_at')
            ->whereNull('unsubscribed_at')
            ->chunkById(100, function ($subscribers) use ($now) {
                foreach ($subscribers as $subscriber) {
                    if (! $this->isDue($subscriber, $now)) {
                        continue;
                    }
                    $this->sendDigest($subscriber);
                }
            });
    }

    private function isDue(NewsroomSubscriber $subscriber, \Illuminate\Support\Carbon $now): bool
    {
        $interval = match ($subscriber->cadence) {
            NewsroomSubscriber::CADENCE_DAILY => 24 * 60 * 60,
            NewsroomSubscriber::CADENCE_WEEKLY => 7 * 24 * 60 * 60,
            default => null,
        };
        if ($interval === null) {
            return false;
        }

        $reference = $subscriber->last_digest_sent_at ?? $subscriber->confirmed_at ?? $subscriber->created_at;
        // abs() because Carbon 3's diffInSeconds is signed — a past
        // reference timestamp returns negative against now().
        return $reference && abs($now->diffInSeconds($reference)) >= $interval;
    }

    private function sendDigest(NewsroomSubscriber $subscriber): void
    {
        // Build the candidate set: all OnePagers published since the
        // subscriber's last digest (or their confirm time on the first
        // cycle) across every company they actively subscribe to.
        $since = $subscriber->last_digest_sent_at ?? $subscriber->confirmed_at;
        if (! $since) {
            return;
        }

        $companyIds = $subscriber->subscriptions()
            ->whereNull('unsubscribed_at')
            ->pluck('company_id');

        if ($companyIds->isEmpty()) {
            return;
        }

        $onePagers = OnePager::query()
            ->with(['company', 'match.publicationItem.source'])
            ->whereIn('company_id', $companyIds)
            ->where('status', OnePager::STATUS_PUBLISHED)
            ->where('published_at', '>', $since)
            ->orderByDesc('published_at')
            ->get();

        if ($onePagers->isEmpty()) {
            // Nothing new — leave last_digest_sent_at alone so the
            // subscriber stays "due" until something publishes.
            return;
        }

        $groups = $onePagers
            ->groupBy('company_id')
            ->map(fn ($items) => [
                'company' => $items->first()->company,
                'onePagers' => $items,
            ])
            ->values();

        Mail::to($subscriber->email)->queue(new SubscriptionDigest($subscriber, $groups));

        $subscriber->forceFill(['last_digest_sent_at' => now()])->save();
    }
}
