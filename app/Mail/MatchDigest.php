<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Periodic email summarizing new matches a team has not yet seen. Sent via
 * {@see SendMatchDigestsJob} on the cadence the user picked.
 */
class MatchDigest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\MatchRecord>  $matches
     */
    public function __construct(
        public User $user,
        public Collection $matches,
        public string $frequency,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->matches->count();
        $label = $count === 1 ? 'opportunity' : 'opportunities';

        return new Envelope(
            subject: "[PrComet] {$count} new outreach {$label}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.matches.digest',
            with: [
                'user' => $this->user,
                'matches' => $this->matches,
                'frequency' => $this->frequency,
            ],
        );
    }
}
