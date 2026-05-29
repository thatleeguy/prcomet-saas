<?php

namespace App\Models;

use Database\Factories\SourceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A publication, podcast, substack, or social account whose output we
 * ingest and analyze for matches against company press releases.
 *
 * Visibility model:
 *   - A source can belong to multiple catalogues (M2M via
 *     source_group_source). Teams subscribe to catalogues; the union
 *     of a team's subscribed catalogues drives which sources are
 *     visible to them.
 *   - The legacy `team` scope (a team's private custom source) still
 *     works as an escape hatch for one-off feeds a customer needs
 *     without operator involvement.
 *
 * Ingestion is per-source, not per-catalogue: each source has one
 * feed_url, one IngestSourceJob, one stream of PublicationItems.
 * Catalogue membership only affects which teams see the resulting
 * items via Source::visibleTo.
 *
 * Soft deletes: removing a source from the operator UI sets
 * deleted_at instead of physically dropping it, so the matches /
 * publication items / wins it produced keep resolving.
 */
class Source extends Model
{
    /** @use HasFactory<SourceFactory> */
    use HasFactory;
    use SoftDeletes;

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

    /**
     * Catalogues this source appears in. A single source can sit in
     * several — eg. an ESG metals podcast lives in both Mining and
     * Clean Energy without being duplicated.
     */
    public function sourceGroups(): BelongsToMany
    {
        return $this->belongsToMany(SourceGroup::class, 'source_group_source')
            ->withTimestamps();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PublicationItem::class);
    }

    /**
     * Sources visible to a given team. Union of:
     *   - Sources in any active catalogue the team currently subscribes
     *     to (where the subscription hasn't expired)
     *   - The team's own private (scope=team) sources
     *   - Legacy global sources not in any catalogue — kept on so a
     *     pre-Catalog install keeps working until the operator pins
     *     each source into one or more groups.
     */
    public function scopeVisibleTo(Builder $query, Team $team): Builder
    {
        $subscribedGroupIds = $team->sourceGroups()
            ->wherePivot('expires_at', null)
            ->orWherePivot('expires_at', '>', now())
            ->pluck('source_groups.id');

        return $query->where(function (Builder $q) use ($team, $subscribedGroupIds) {
            $q->whereExists(function ($sub) use ($subscribedGroupIds) {
                $sub->select('source_id')
                    ->from('source_group_source')
                    ->whereColumn('source_group_source.source_id', 'sources.id')
                    ->whereIn('source_group_source.source_group_id', $subscribedGroupIds);
            })
                ->orWhere(function (Builder $inner) use ($team) {
                    $inner->where('scope', self::SCOPE_TEAM)
                        ->where('team_id', $team->id);
                })
                ->orWhere(function (Builder $legacy) {
                    // Global sources not in any catalogue stay visible to
                    // everyone until the operator pins them. Avoids a
                    // pre-Catalog install going dark mid-migration.
                    $legacy->where('scope', self::SCOPE_GLOBAL)
                        ->whereNotExists(function ($sub) {
                            $sub->select('source_id')
                                ->from('source_group_source')
                                ->whereColumn('source_group_source.source_id', 'sources.id');
                        });
                });
        });
    }
}
