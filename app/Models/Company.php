<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A monitored entity — a Junior Mining Company in the v0 wedge, but the
 * model is generic enough for cannabis, biotech, crypto down the line.
 *
 * Belongs to a Team (one customer). Team's `max_companies` enforces the seat
 * count at creation time via {@see Team::canAddCompany()}.
 */
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
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
    ];

    protected function casts(): array
    {
        return [
            'secondary_feeds' => 'array',
            'sector_tags' => 'array',
            'is_active' => 'boolean',
            'last_ingested_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function pressReleases(): HasMany
    {
        return $this->hasMany(PressRelease::class);
    }
}
