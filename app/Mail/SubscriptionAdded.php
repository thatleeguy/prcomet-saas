<?php

namespace App\Mail;

use App\Mail\Concerns\CoBrandedMail;
use App\Models\Company;
use App\Models\NewsroomSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when an already-confirmed subscriber adds another company to
 * their network. No double opt-in — they're a known good identity.
 */
class SubscriptionAdded extends Mailable
{
    use Queueable, SerializesModels, CoBrandedMail;

    public function __construct(
        public NewsroomSubscriber $subscriber,
        public Company $company,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->coBrandedFrom($this->company),
            replyTo: array_filter([$this->coBrandedReplyTo($this->company)]),
            subject: "Added {$this->company->name} to your subscriptions",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.newsroom.subscription-added',
            with: [
                'subscriber' => $this->subscriber,
                'company' => $this->company,
                'manageUrl' => $this->subscriber->manageUrl(),
            ],
        );
    }
}
