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
 * Sent to the team owner once a super-admin has activated their workspace.
 */
class TeamActivated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Team $team) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PrComet] Your account is active',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.teams.activated',
            with: [
                'team' => $this->team,
                'seats' => $this->team->max_companies,
            ],
        );
    }
}
