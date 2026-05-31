<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'title', 'excerpt', 'body_md', 'category', 'tags',
        'author_name', 'created_by_id', 'status', 'published_at',
        'meta_title', 'meta_description', 'og_image_path', 'view_count',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            if (blank($article->slug) && filled($article->title)) {
                $article->slug = static::uniqueSlug(Str::slug($article->title), $article->id);
            }
        });
    }

    /**
     * Use the slug as the public route key.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Only published articles whose publish date has passed.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Estimated reading time in minutes, based on ~200 wpm.
     */
    public function readingTime(): int
    {
        $words = str_word_count(strip_tags((string) $this->body_md));

        return max(1, (int) ceil($words / 200));
    }

    /**
     * The description used for meta/SEO tags, falling back to the excerpt.
     */
    public function metaDescription(): ?string
    {
        return $this->meta_description
            ?: ($this->excerpt ? Str::limit(strip_tags($this->excerpt), 155) : null);
    }

    protected static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = $base ?: 'article';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
