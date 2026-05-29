<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Operator-only heads-up about LLM spend.
 *
 * Two flavours are emitted by LlmUsageTracker:
 *   - "Team approaching daily LLM spend"  (per-team soft alert)
 *   - "System approaching daily LLM cap"  (system-wide pre-throttle)
 *
 * The body string is composed by the tracker so this class stays a
 * dumb mail wrapper. Recipients come from config/llm.php or fall back
 * to every is_admin user.
 */
class LlmBudgetAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $subjectLine,
        public readonly string $body,
    ) {}

    /** @return array<int,string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[PrComet] '.$this->subjectLine)
            ->line($this->body)
            ->line('Review usage at /manage. This is an operator-only alert — customers see nothing change.');
    }
}
