<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single AI-generated pitch email for a match.
 *
 * Keyed (match, tone) so the user can keep one formal, one direct,
 * and one casual variant for the same match. Regenerating the same
 * tone overwrites in place.
 */
class PitchDraft extends Model
{
    public const TONE_FORMAL = 'formal';
    public const TONE_DIRECT = 'direct';
    public const TONE_CASUAL = 'casual';

    public const TONES = [
        self::TONE_FORMAL => 'Formal',
        self::TONE_DIRECT => 'Direct',
        self::TONE_CASUAL => 'Casual',
    ];

    protected $fillable = [
        'match_id',
        'tone',
        'subject',
        'body',
        'generated_by_user_id',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchRecord::class, 'match_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    /**
     * Build a mailto: URL that opens the user's default mail client
     * with To, Subject, and Body pre-filled. The recipient comes from
     * the journalist record when we know it.
     */
    public function mailtoUrl(?string $recipient = null): string
    {
        $params = http_build_query([
            'subject' => $this->subject,
            'body' => $this->body,
        ], '', '&', PHP_QUERY_RFC3986);

        $to = $recipient ? rawurlencode($recipient) : '';

        return "mailto:{$to}?{$params}";
    }
}
