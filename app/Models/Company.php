<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * A monitored entity — a Junior Mining Company in the v0 wedge, but the
 * model is generic enough for cannabis, biotech, crypto down the line.
 *
 * Belongs to a Team (one customer). Team's `max_companies` enforces the seat
 * count at creation time via {@see Team::canAddCompany()}.
 *
 * Branding fields (logo, header, accent color, tagline, etc.) feed the
 * one-pager renderer. They live on the Company, not the OnePager, so a brand
 * refresh propagates to every page automatically.
 */
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'newsroom_published',
        'ticker',
        'exchange',
        'website',
        'rss_feed_url',
        'secondary_feeds',
        'ir_contact_name',
        'ir_contact_email',
        'ir_contact_phone',
        'sector_tags',
        'is_active',
        'last_ingested_at',
        // Branding
        'logo_path',
        'header_image_path',
        'newsroom_header_image_path',
        'accent_color',
        'tagline',
        'description_md',
        'press_contact_email',
        'social_links',
        // Blanket media release inherited by library assets
        'blanket_media_release_text',
        'blanket_media_release_file_path',
    ];

    protected function casts(): array
    {
        return [
            'secondary_feeds' => 'array',
            'sector_tags' => 'array',
            'social_links' => 'array',
            'is_active' => 'boolean',
            'newsroom_published' => 'boolean',
            'last_ingested_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        // Public newsroom routes bind by slug; everything else
        // continues to use the id.
        return request()->routeIs('newsroom.*') ? 'slug' : 'id';
    }

    protected static function booted(): void
    {
        // Auto-slug on create from the name so new companies always
        // have a stable newsroom URL. Per-row uniqueness loop in case
        // two companies share a name across teams.
        static::creating(function (Company $c) {
            if (! $c->slug) {
                $base = \Illuminate\Support\Str::slug((string) $c->name) ?: 'company';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $c->slug = $slug;
            }
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function pressReleases(): HasMany
    {
        return $this->hasMany(PressRelease::class);
    }

    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    public function onePagers(): HasMany
    {
        return $this->hasMany(OnePager::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path
            ? Storage::disk(config('filesystems.default'))->url($this->logo_path)
            : null;
    }

    public function headerImageUrl(): ?string
    {
        return $this->header_image_path
            ? Storage::disk(config('filesystems.default'))->url($this->header_image_path)
            : null;
    }

    /**
     * Header image for the public newsroom page. Falls back to the
     * standard header image when the newsroom-specific one is unset,
     * so customers who don't care about per-surface customisation
     * get consistent branding for free.
     */
    public function newsroomHeaderImageUrl(): ?string
    {
        if ($this->newsroom_header_image_path) {
            return Storage::disk(config('filesystems.default'))->url($this->newsroom_header_image_path);
        }
        return $this->headerImageUrl();
    }

    public function blanketMediaReleaseFileUrl(): ?string
    {
        return $this->blanket_media_release_file_path
            ? Storage::disk(config('filesystems.default'))->url($this->blanket_media_release_file_path)
            : null;
    }
}
