<?php

namespace App\Models;

use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
