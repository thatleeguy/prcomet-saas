<?php

namespace App\Mail;

use App\Models\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to all super-admins when a new team signs up. The activation
 * workflow is manual pre-PMF, so admins need to know to take action.
 */
class TeamSignedUp extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Team $team) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[PrComet] New signup: {$this->team->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.teams.signed-up',
            with: [
                'team' => $this->team,
                'owner' => $this->team->owner,
            ],
        );
    }
}
