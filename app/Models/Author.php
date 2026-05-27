<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A journalist, podcast host, substack writer, or social commentator whose
 * body of work we analyze for receptivity to a company's pitch.
 *
 * Slugged on save so the same person can be deduplicated across appearances.
 */
class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    protected $fillable = [
        'primary_source_id',
        'name',
        'slug',
        'bio',
        'x_handle',
        'email',
        'website',
        'body_of_work_summary',
        'body_of_work_updated_at',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'body_of_work_updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Author $author) {
            if ($author->slug === null || $author->slug === '') {
                $author->slug = Str::slug($author->name);
            }
        });
    }

    public function primarySource(): BelongsTo
    {
        return $this->belongsTo(Source::class, 'primary_source_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PublicationItem::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(ExtractedClaim::class);
    }
}
