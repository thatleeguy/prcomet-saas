<?php

namespace App\Models;

use Database\Factories\MatchEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit trail for actions taken on a MatchRecord: saved, contacted, dismissed,
 * placed, or a free-form note. Used by the activity feed and outcome metrics.
 */
class MatchEvent extends Model
{
    /** @use HasFactory<MatchEventFactory> */
    use HasFactory;

    public const TYPE_SAVED = 'saved';
    public const TYPE_CONTACTED = 'contacted';
    public const TYPE_DISMISSED = 'dismissed';
    public const TYPE_PLACED = 'placed';
    public const TYPE_NOTE = 'note';

    protected $fillable = [
        'match_id',
        'user_id',
        'event_type',
        'notes_md',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchRecord::class, 'match_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
