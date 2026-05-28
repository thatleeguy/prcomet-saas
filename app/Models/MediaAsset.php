<?php

namespace App\Models;

use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * A single piece of media in a company's Media Library — image, logo, header,
 * PDF, pull quote, or external link. The Library is the substance; one-pagers
 * pick from it.
 *
 * `source` records provenance: 'manual' for uploads, 'press_release' for
 * quotes auto-extracted by the analysis pipeline. The latter link back to
 * their originating PressRelease so users can audit where a quote came from.
 */
class MediaAsset extends Model
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory;

    public const TYPE_IMAGE = 'image';
    public const TYPE_LOGO = 'logo';
    public const TYPE_HEADER = 'header';
    public const TYPE_PDF = 'pdf';
    public const TYPE_QUOTE = 'quote';
    public const TYPE_LINK = 'link';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_PRESS_RELEASE = 'press_release';

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'description',
        'credit',
        'credit_url',
        'uses_blanket_release',
        'media_release_text',
        'media_release_file_path',
        'file_path',
        'mime_type',
        'size_bytes',
        'width_px',
        'height_px',
        'url',
        'quote_text',
        'quote_attribution',
        'tags',
        'is_active',
        'source',
        'source_press_release_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_active' => 'boolean',
            'uses_blanket_release' => 'boolean',
            'size_bytes' => 'integer',
            'width_px' => 'integer',
            'height_px' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sourcePressRelease(): BelongsTo
    {
        return $this->belongsTo(PressRelease::class, 'source_press_release_id');
    }

    public function onePagers(): BelongsToMany
    {
        return $this->belongsToMany(OnePager::class, 'one_pager_assets')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(MediaAssetRevision::class)->orderByDesc('created_at');
    }

    /**
     * URL for the per-asset media-release PDF (if any). Same disk routing
     * as the asset itself.
     */
    public function mediaReleaseFileUrl(): ?string
    {
        if (! $this->media_release_file_path) {
            return null;
        }

        return Storage::disk(config('filesystems.default'))->url($this->media_release_file_path);
    }

    /**
     * Resolve the release text that applies to this asset — either its own
     * override or the company's blanket text. Returns null if neither is set.
     */
    public function effectiveReleaseText(): ?string
    {
        if (! $this->uses_blanket_release && filled($this->media_release_text)) {
            return $this->media_release_text;
        }

        return $this->company?->blanket_media_release_text;
    }

    /**
     * Resolve the release PDF that applies — per-asset override wins, then
     * falls back to the company's blanket PDF.
     */
    public function effectiveReleaseFileUrl(): ?string
    {
        if (! $this->uses_blanket_release && $this->media_release_file_path) {
            return $this->mediaReleaseFileUrl();
        }

        return $this->company?->blanketMediaReleaseFileUrl();
    }

    /**
     * Snapshot the current file metadata into a revision row before the
     * caller overwrites file_path with a new upload. Returns the revision
     * (or null if the asset doesn't currently have a file).
     */
    public function snapshotCurrentAsRevision(?int $userId = null, ?string $notes = null): ?MediaAssetRevision
    {
        if (! $this->file_path) {
            return null;
        }

        return $this->revisions()->create([
            'uploaded_by_user_id' => $userId,
            'file_path' => $this->file_path,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'width_px' => $this->width_px,
            'height_px' => $this->height_px,
            'notes' => $notes,
        ]);
    }

    /**
     * Public URL for file-backed assets. Routes through Laravel's filesystem
     * abstraction so the same code works in dev (local disk) and prod (S3).
     */
    public function publicUrl(): ?string
    {
        if ($this->type === self::TYPE_LINK) {
            return $this->url;
        }

        if (! $this->file_path) {
            return null;
        }

        return Storage::disk(config('filesystems.default'))->url($this->file_path);
    }

    /**
     * Convenience: is this a file-backed asset (vs. text/link)?
     */
    public function isFileBacked(): bool
    {
        return in_array($this->type, [self::TYPE_IMAGE, self::TYPE_LOGO, self::TYPE_HEADER, self::TYPE_PDF], true);
    }

    public function deleteFile(): void
    {
        if ($this->file_path) {
            Storage::disk(config('filesystems.default'))->delete($this->file_path);
        }
    }
}
