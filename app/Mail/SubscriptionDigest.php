<?php

namespace App\Mail;

use App\Mail\Concerns\CoBrandedMail;
use App\Models\NewsroomSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Aggregated digest of new one-pagers across every company the
 * subscriber follows, sent at their chosen cadence (daily / weekly).
 *
 * Co-branding is collapsed here because the email represents
 * multiple companies, not one. From is "PrComet <subscriptions@…>";
 * the subject reflects how many companies published this cycle.
 */
class SubscriptionDigest extends Mailable
{
    use Queueable, SerializesModels, CoBrandedMail;

    /**
     * @param Collection<int, array{company:\App\Models\Company, onePagers:Collection<int,\App\Models\OnePager>}> $groups
     */
    public function __construct(
        public NewsroomSubscriber $subscriber,
        public Collection $groups,
    ) {}

    public function envelope(): Envelope
    {
        $totalStories = $this->groups->sum(fn ($g) => $g['onePagers']->count());
        $companies = $this->groups->count();

        $subject = $companies === 1
            ? $this->groups->first()['company']->name.': '.$totalStories.' new '.\Illuminate\Support\Str::plural('story', $totalStories)
            : "Your PrComet digest — {$totalStories} ".\Illuminate\Support\Str::plural('story', $totalStories)." from {$companies} ".\Illuminate\Support\Str::plural('company', $companies);

        $fromAddress = (string) (config('mail.from.address') ?: 'hello@prcomet.com');

        return new Envelope(
            from: new Address($fromAddress, 'PrComet'),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.newsroom.digest',
            with: [
                'subscriber' => $this->subscriber,
                'groups' => $this->groups,
                'manageUrl' => $this->subscriber->manageUrl(),
                'unsubscribeUrl' => $this->subscriber->unsubscribeUrl(),
            ],
        );
    }
}
