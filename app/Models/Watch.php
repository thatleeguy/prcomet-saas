<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A named alert that watches the ingested corpus for specific terms.
 *
 * Belongs to a company (the workspace it lives in). The `terms` array
 * stores literal strings — primary name plus any aliases the user wants
 * (so a "Newmont" watch can include "Newmont Mining", "Newmont Goldcorp",
 * etc.). Matching is case-insensitive whole-word.
 *
 * `mode` records the user's chosen matching strategy:
 *   - literal      → free, fast, exact-string only
 *   - literal_llm  → literal first, then Claude confirms each hit. Requires
 *                    the team's llm_observatory_enabled flag; falls back to
 *                    literal at scan time if the team isn't entitled.
 */
class Watch extends Model
{
    public const KIND_COMPANY = 'company';
    public const KIND_LOCATION = 'location';
    public const KIND_PRODUCT = 'product';
    public const KIND_TERM = 'term';

    public const MODE_LITERAL = 'literal';
    public const MODE_LITERAL_LLM = 'literal_llm';

    protected $fillable = [
        'company_id',
        'created_by_user_id',
        'name',
        'kind',
        'terms',
        'mode',
        'is_active',
        'last_matched_at',
        'hit_count',
    ];

    /**
     * Default attribute values applied to newly-instantiated models.
     * Mirrors the column defaults so attributes are sane before the row
     * round-trips through the database.
     */
    protected $attributes = [
        'is_active' => true,
        'hit_count' => 0,
        'mode' => 'literal',
        'kind' => 'term',
    ];

    protected function casts(): array
    {
        return [
            'terms' => 'array',
            'is_active' => 'boolean',
            'last_matched_at' => 'datetime',
            'hit_count' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function hits(): HasMany
    {
        return $this->hasMany(WatchHit::class);
    }

    /**
     * Resolve the effective scan mode for this watch right now, taking the
     * team's entitlement into account. A watch saved as literal_llm
     * downgrades to literal at scan time if the team has lost (or never
     * had) the LLM observatory upgrade — the stored intent is preserved
     * so re-enabling the upgrade restores LLM checks without re-editing.
     */
    public function effectiveMode(): string
    {
        if ($this->mode === self::MODE_LITERAL_LLM
            && $this->company?->team?->llmObservatoryEnabled()) {
            return self::MODE_LITERAL_LLM;
        }

        return self::MODE_LITERAL;
    }

    public function llmEnabled(): bool
    {
        return $this->effectiveMode() === self::MODE_LITERAL_LLM;
    }
}
