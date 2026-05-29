<?php

namespace App\Mail;

use App\Mail\Concerns\CoBrandedMail;
use App\Models\Company;
use App\Models\NewsroomSubscriber;
use App\Models\OnePager;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to cadence=instant subscribers when a one-pager goes live.
 * Single company per email — cadence=daily/weekly subscribers get a
 * batched digest instead.
 */
class InstantPublishNotice extends Mailable
{
    use Queueable, SerializesModels, CoBrandedMail;

    public function __construct(
        public NewsroomSubscriber $subscriber,
        public Company $company,
        public OnePager $onePager,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->coBrandedFrom($this->company),
            replyTo: array_filter([$this->coBrandedReplyTo($this->company)]),
            subject: $this->onePager->displayTitle(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.newsroom.instant-publish',
            with: [
                'subscriber' => $this->subscriber,
                'company' => $this->company,
                'onePager' => $this->onePager,
                'manageUrl' => $this->subscriber->manageUrl(),
                'unsubscribeUrl' => $this->subscriber->unsubscribeUrl(),
            ],
        );
    }
}
