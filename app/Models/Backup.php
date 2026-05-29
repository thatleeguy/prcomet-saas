<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Metadata row for a single backup archive sitting on the configured
 * backup disk. The archive itself is a zip — see BackupService::create.
 */
class Backup extends Model
{
    protected $fillable = [
        'filename',
        'disk',
        'db_driver',
        'includes_files',
        'size_bytes',
        'created_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'includes_files' => 'boolean',
            'size_bytes' => 'integer',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function disk()
    {
        return Storage::disk($this->disk);
    }

    public function exists(): bool
    {
        return $this->disk()->exists($this->filename);
    }

    public function humanSize(): string
    {
        $bytes = $this->size_bytes ?? 0;
        if ($bytes >= 1_073_741_824) return number_format($bytes / 1_073_741_824, 2).' GB';
        if ($bytes >= 1_048_576) return number_format($bytes / 1_048_576, 1).' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 0).' KB';
        return $bytes.' B';
    }
}
