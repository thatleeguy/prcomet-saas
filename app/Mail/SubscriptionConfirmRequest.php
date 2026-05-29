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
 * Sent when a brand-new email subscribes via /newsroom/{slug}.
 *
 * Double-opt-in: the subscriber's record exists but confirmed_at
 * stays null until they click the link. No digest dispatch fires
 * until confirmation lands.
 */
class SubscriptionConfirmRequest extends Mailable
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
            subject: "Confirm your {$this->company->name} subscription",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.newsroom.subscription-confirm-request',
            with: [
                'subscriber' => $this->subscriber,
                'company' => $this->company,
                'confirmUrl' => $this->subscriber->confirmUrl(),
            ],
        );
    }
}
