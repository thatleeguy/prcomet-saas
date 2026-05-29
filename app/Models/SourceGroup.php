<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A named catalogue of sources teams can subscribe to.
 *
 * Examples seeded by SourceGroupSeeder:
 *   - "Mining Publications" (free, default for JMC customers)
 *   - "Oil & Gas Publications" (premium add-on)
 *   - "Clean Energy" (premium add-on)
 *
 * Premium groups carry a price and surface in the customer Catalog
 * panel as upsells. Operators grant access via the Filament team
 * resource. Soft-deleted groups vanish from the operator UI and the
 * subscription pivot but matches that already referenced contained
 * sources keep resolving via the publication_item → source FK.
 */
class SourceGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_premium',
        'is_active',
        'monthly_price_cents',
        'icon_emoji',
        'accent_color',
    ];

    protected function casts(): array
    {
        return [
            'is_premium' => 'boolean',
            'is_active' => 'boolean',
            'monthly_price_cents' => 'integer',
        ];
    }

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'source_group_subscriptions')
            ->withPivot(['is_complimentary', 'subscribed_at', 'expires_at', 'granted_by_user_id', 'notes'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Friendly price label for the operator UI. Returns "Free" for
     * non-premium groups, "$X / mo" for priced premium groups, and
     * "Premium" when premium but no price set yet.
     */
    public function priceLabel(): string
    {
        if (! $this->is_premium) {
            return 'Free';
        }
        if ($this->monthly_price_cents) {
            return '$'.number_format($this->monthly_price_cents / 100, 0).' / mo';
        }
        return 'Premium';
    }
}
