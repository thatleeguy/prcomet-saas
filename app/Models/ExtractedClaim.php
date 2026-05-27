<?php

namespace App\Models;

use Database\Factories\ExtractedClaimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One testable claim or prediction extracted from a publication item.
 *
 * Forward-looking predictions get verified asynchronously against external
 * data (commodity prices etc.) and set verified_outcome to one of:
 * - correct: prediction matched reality
 * - incorrect: prediction was wrong
 * - partial: directionally right but magnitude/timing off
 * - unverifiable: cannot be checked from available data
 */
class ExtractedClaim extends Model
{
    /** @use HasFactory<ExtractedClaimFactory> */
    use HasFactory;

    public const VERIFIED_CORRECT = 'correct';
    public const VERIFIED_INCORRECT = 'incorrect';
    public const VERIFIED_PARTIAL = 'partial';
    public const VERIFIED_UNVERIFIABLE = 'unverifiable';

    protected $fillable = [
        'publication_item_id',
        'author_id',
        'claim_text',
        'topic',
        'stance',
        'predicted_outcome',
        'timeframe',
        'verified_outcome',
        'verified_at',
        'verification_evidence',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'verification_evidence' => 'array',
        ];
    }

    public function publicationItem(): BelongsTo
    {
        return $this->belongsTo(PublicationItem::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
