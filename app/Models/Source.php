<?php

namespace App\Models;

use Database\Factories\SourceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A publication, podcast, substack, or social account whose output we ingest
 * and analyze for matches against company press releases.
 *
 * Two scopes:
 * - `global` — admin-curated and shared across all teams.
 * - `team`   — a single team's private custom source.
 *
 * Visibility for a team is the union of global + their own team sources, via
 * the {@see visibleTo} scope.
 */
class Source extends Model
{
    /** @use HasFactory<SourceFactory> */
    use HasFactory;

    public const SCOPE_GLOBAL = 'global';
    public const SCOPE_TEAM = 'team';

    public const TYPE_PUBLICATION = 'publication';
    public const TYPE_PODCAST = 'podcast';
    public const TYPE_SUBSTACK = 'substack';
    public const TYPE_X = 'x';
    public const TYPE_YOUTUBE = 'youtube';

    protected $fillable = [
        'scope',
        'team_id',
        'type',
        'name',
        'base_url',
        'feed_url',
        'ingest_strategy',
        'ingest_config',
        'tags',
        'is_active',
        'last_ingested_at',
    ];

    protected function casts(): array
    {
        return [
            'ingest_config' => 'array',
            'tags' => 'array',
            'is_active' => 'boolean',
            'last_ingested_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PublicationItem::class);
    }

    /**
     * Sources visible to a given team — globals plus that team's own.
     */
    public function scopeVisibleTo(Builder $query, Team $team): Builder
    {
        return $query->where(function (Builder $q) use ($team) {
            $q->where('scope', self::SCOPE_GLOBAL)
                ->orWhere(function (Builder $inner) use ($team) {
                    $inner->where('scope', self::SCOPE_TEAM)->where('team_id', $team->id);
                });
        });
    }
}
