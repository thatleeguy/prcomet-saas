<?php

namespace App\Models;

use Database\Factories\OnePagerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Shareable one-pager for a specific match.
 *
 * Lifecycle:
 *  - draft (created automatically when a match leaves status=new)
 *  - published (URL is live, view tracking is active)
 *  - unpublished (URL 404s; existing view rows remain for the record)
 *
 * The UUID is generated at creation time and stable forever, so the URL
 * doesn't change even if the user un/republishes.
 */
class OnePager extends Model
{
    /** @use HasFactory<OnePagerFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_UNPUBLISHED = 'unpublished';

    protected $fillable = [
        'uuid',
        'match_id',
        'company_id',
        'title',
        'created_by_id',
        'note_md',
        'status',
        'published_at',
        'view_count',
        'unique_view_count',
        'last_viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'last_viewed_at' => 'datetime',
        ];
    }

    /** Use UUID for public route binding. */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function (OnePager $page) {
            if (! $page->uuid) {
                $page->uuid = (string) Str::uuid();
            }
        });
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchRecord::class, 'match_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'one_pager_assets')
            ->withPivot('sort_order')
            ->orderBy('one_pager_assets.sort_order')
            ->withTimestamps();
    }

    public function views(): HasMany
    {
        return $this->hasMany(OnePagerView::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function publicUrl(): string
    {
        return route('onepagers.show', $this->uuid);
    }

    /**
     * Did this page originate from a match, or was it created standalone?
     * Used by the editor view to decide whether to show match-context
     * (target journalist, score, suggested angle) or a title field.
     */
    public function isStandalone(): bool
    {
        return $this->match_id === null;
    }

    /**
     * Human label for the page in lists and breadcrumbs. Falls back through
     * the saved title, the originating match's headline, and finally a
     * generic placeholder so the UI never shows blank.
     */
    public function displayTitle(): string
    {
        if (filled($this->title)) {
            return $this->title;
        }

        $headline = $this->match?->publicationItem?->title
            ?? $this->match?->pressRelease?->title;

        return $headline ?? 'Untitled one-pager';
    }
}
