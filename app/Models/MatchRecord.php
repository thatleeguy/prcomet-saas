<?php

namespace App\Models;

use Database\Factories\MatchRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A discovered outreach opportunity — one match between a Company's press
 * release and a PublicationItem (article / podcast episode / etc).
 *
 * The rationale + suggested_angle are the *product*. Citations link back to
 * the original publication so the user can verify every claim in the brief.
 *
 * `MatchRecord` because `Match` is a reserved keyword in PHP 8+.
 * Database table is `matches` (the natural name).
 */
class MatchRecord extends Model
{
    /** @use HasFactory<MatchRecordFactory> */
    use HasFactory;

    protected $table = 'matches';

    public const STATUS_NEW = 'new';
    public const STATUS_SAVED = 'saved';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_DISMISSED = 'dismissed';
    public const STATUS_PLACED = 'placed';

    protected $fillable = [
        'company_id',
        'press_release_id',
        'publication_item_id',
        'author_id',
        'score',
        'rationale_md',
        'suggested_angle_md',
        'citations',
        'status',
        'placement_url',
        'placement_title',
        'placement_description',
        'placement_published_at',
        'placement_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'citations' => 'array',
            'placement_published_at' => 'datetime',
            'placement_fetched_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function pressRelease(): BelongsTo
    {
        return $this->belongsTo(PressRelease::class);
    }

    public function publicationItem(): BelongsTo
    {
        return $this->belongsTo(PublicationItem::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id');
    }
}
