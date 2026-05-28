<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A single occurrence of a watch term inside one piece of content.
 *
 * Polymorphic so the same row layout serves PublicationItems and
 * PressReleases. The (watch_id, content_type, content_id) tuple is
 * unique — the scanner uses upsert semantics so re-running it over the
 * same corpus doesn't multiply rows.
 */
class WatchHit extends Model
{
    public const TYPE_PUBLICATION_ITEM = 'publication_item';
    public const TYPE_PRESS_RELEASE = 'press_release';

    public const MAP = [
        self::TYPE_PUBLICATION_ITEM => PublicationItem::class,
        self::TYPE_PRESS_RELEASE => PressRelease::class,
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
    ];

    protected function casts(): array
    {
        return [
            'matched_at' => 'datetime',
            'confirmed_by_llm' => 'boolean',
        ];
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
        if ($row instanceof PressRelease) {
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
            self::TYPE_PRESS_RELEASE => 'Press release',
            default => $this->content_type,
        };
    }
}
