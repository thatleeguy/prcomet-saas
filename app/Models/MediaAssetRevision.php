<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A frozen snapshot of a media asset's file at a point in time.
 *
 * Rows are created when a user uploads a replacement file — the *previous*
 * file's path and metadata get stashed here before the parent row is
 * overwritten. The file itself stays on disk so reverts are cheap (no
 * re-upload required) and so editorial history isn't quietly destroyed.
 */
class MediaAssetRevision extends Model
{
    protected $fillable = [
        'media_asset_id',
        'uploaded_by_user_id',
        'file_path',
        'mime_type',
        'size_bytes',
        'width_px',
        'height_px',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width_px' => 'integer',
            'height_px' => 'integer',
        ];
    }

    public function mediaAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /**
     * Public URL for the historical file — the same disk routing as a
     * current asset, since "old" files still live on the same disk.
     */
    public function publicUrl(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::disk(config('filesystems.default'))->url($this->file_path);
    }

    /**
     * Friendly file size like "1.2 MB" — used in the revisions list.
     */
    public function humanSize(): string
    {
        $bytes = $this->size_bytes ?? 0;

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }
        return $bytes.' B';
    }
}
