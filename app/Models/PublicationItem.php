<?php

namespace App\Models;

use Database\Factories\PublicationItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One article / podcast episode / substack post / tweet / video pulled from a
 * Source. The unit we match company press releases against.
 *
 * `body_text` is the article body (plaintext). `transcript` is filled for
 * podcast/youtube once transcription runs (post-Phase-9 add-on; currently
 * podcasts ingest with body_text=description).
 */
class PublicationItem extends Model
{
    /** @use HasFactory<PublicationItemFactory> */
    use HasFactory;

    public const ANALYSIS_PENDING = 'pending';
    public const ANALYSIS_RUNNING = 'analyzing';
    public const ANALYSIS_DONE = 'done';
    public const ANALYSIS_FAILED = 'failed';

    protected $fillable = [
        'source_id',
        'author_id',
        'external_guid',
        'url',
        'title',
        'body_text',
        'transcript',
        'published_at',
        'analysis_status',
        'analyzed_at',
        'extracted_entities',
        'extracted_topics',
        'stance',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'analyzed_at' => 'datetime',
            'extracted_entities' => 'array',
            'extracted_topics' => 'array',
            'embedding' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ExtractedClaim::class);
    }
}
