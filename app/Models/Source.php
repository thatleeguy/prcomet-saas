<?php

namespace App\Models;

use Database\Factories\SourceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A publication, podcast, substack, or social account whose output we
 * ingest and analyze for matches against company press releases.
 *
 * Visibility model:
 *   - A source belongs to one SourceGroup (a catalogue like "Mining
 *     Publications"). Teams subscribe to groups; that subscription
 *     drives which sources are visible to which teams.
 *   - The legacy `team` scope (a team's private custom source) still
 *     works as an escape hatch for one-off feeds a customer needs
 *     without operator involvement.
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
        'source_group_id',
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

    public function sourceGroup(): BelongsTo
    {
        return $this->belongsTo(SourceGroup::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PublicationItem::class);
    }

    /**
     * Sources visible to a given team. Union of:
     *   - Sources in any active group the team currently subscribes to
     *   - The team's own private (scope=team) sources
     *   - Legacy global sources NOT yet assigned to a group — kept on
     *     so a pre-Catalog install keeps working until the operator
     *     pins each source into a group.
     */
    public function scopeVisibleTo(Builder $query, Team $team): Builder
    {
        $subscribedGroupIds = $team->sourceGroups()
            ->wherePivot('expires_at', null)
            ->orWherePivot('expires_at', '>', now())
            ->pluck('source_groups.id');

        return $query->where(function (Builder $q) use ($team, $subscribedGroupIds) {
            $q->whereIn('source_group_id', $subscribedGroupIds)
                ->orWhere(function (Builder $inner) use ($team) {
                    $inner->where('scope', self::SCOPE_TEAM)
                        ->where('team_id', $team->id);
                })
                ->orWhere(function (Builder $legacy) {
                    // Unassigned global sources stay visible to everyone until
                    // the operator assigns them to a group. Stops a fresh
                    // install from looking empty during migration.
                    $legacy->where('scope', self::SCOPE_GLOBAL)
                        ->whereNull('source_group_id');
                });
        });
    }
}
