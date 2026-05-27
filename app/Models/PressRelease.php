<?php

namespace App\Models;

use Database\Factories\PressReleaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A press release ingested from a Company's RSS feed.
 *
 * `external_guid` is the source feed's GUID/link — used to deduplicate so that
 * re-polling the feed never creates duplicates. Body text is plaintext, body
 * html is the original markup; both are kept for analysis fallbacks.
 *
 * Phase 5 fills extracted_entities / topics / stance / key_claims / embedding.
 */
class PressRelease extends Model
{
    /** @use HasFactory<PressReleaseFactory> */
    use HasFactory;

    public const ANALYSIS_PENDING = 'pending';
    public const ANALYSIS_RUNNING = 'analyzing';
    public const ANALYSIS_DONE = 'done';
    public const ANALYSIS_FAILED = 'failed';

    protected $fillable = [
        'company_id',
        'external_guid',
        'source_url',
        'title',
        'body_html',
        'body_text',
        'published_at',
        'analysis_status',
        'analyzed_at',
        'extracted_entities',
        'extracted_topics',
        'stance',
        'key_claims',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'analyzed_at' => 'datetime',
            'extracted_entities' => 'array',
            'extracted_topics' => 'array',
            'key_claims' => 'array',
            'embedding' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchRecord::class, 'press_release_id');
    }
}
