<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A single occurrence of a watch term inside one piece of content.
 *
 * v0 only records hits against PublicationItems (the journalist
 * corpus). The polymorphic shape — content_type + content_id — is
 * kept so other content types can be added later without a migration.
 * The (watch_id, content_type, content_id) tuple is unique — the
 * scanner uses upsert semantics so re-running it over the same corpus
 * doesn't multiply rows.
 */
class WatchHit extends Model
{
    public const TYPE_PUBLICATION_ITEM = 'publication_item';

    public const MAP = [
        self::TYPE_PUBLICATION_ITEM => PublicationItem::class,
    ];

    protected $fillable = [
        'watch_id',
        'content_type',
        'content_id',
        'matched_term',
        'context_snippet',
        'confirmed_by_llm',
        'llm_reasoning',
        'matched_at',
        'seen_at',
    ];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
            'seen_at' => 'datetime',
            'confirmed_by_llm' => 'boolean',
        ];
    }

    public function isSeen(): bool
    {
        return $this->seen_at !== null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('seen_at');
    }

    public function markSeen(): void
    {
        if ($this->seen_at === null) {
            $this->forceFill(['seen_at' => now()])->save();
        }
    }

    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }

    /**
     * Resolve the underlying content row via the type map. Not a true
     * Eloquent morphTo because we use string aliases (publication_item /
     * press_release) rather than fully-qualified class names — keeps the
     * table compact and the alias stable across namespace refactors.
     */
    public function content(): ?Model
    {
        $class = self::MAP[$this->content_type] ?? null;

        return $class ? $class::find($this->content_id) : null;
    }

    /**
     * Public URL of the underlying content, for the hit-row "Open" link.
     */
    public function contentUrl(): ?string
    {
        $row = $this->content();

        if ($row instanceof PublicationItem) {
            return $row->url;
        }

        return null;
    }

    /**
     * Short label for the content row.
     */
    public function contentTitle(): string
    {
        $row = $this->content();

        return $row?->title ?? '(content removed)';
    }

    public function contentTypeLabel(): string
    {
        return match ($this->content_type) {
            self::TYPE_PUBLICATION_ITEM => 'Publication item',
            default => $this->content_type,
        };
    }
}
