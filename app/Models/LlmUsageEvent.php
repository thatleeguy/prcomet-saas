<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per LLM call. Append-only; never updated or backfilled.
 *
 * The cost_cents column captures the cost we charge ourselves at the
 * moment of writing — using pricing from config/llm.php. We don't
 * re-price retrospectively if model pricing changes, since the ledger
 * should reflect what was actually spent at the time of the call.
 */
class LlmUsageEvent extends Model
{
    public const FEATURE_MATCH_BRIEF = 'match_brief';
    public const FEATURE_WATCH_HIT_CONFIRM = 'watch_hit_confirm';

    protected $fillable = [
        'team_id',
        'user_id',
        'feature',
        'model',
        'input_tokens',
        'output_tokens',
        'cost_cents',
    ];

    protected function casts(): array
    {
        return [
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'cost_cents' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sum of cost_cents across all rows after the given time. Used by
     * the budget enforcer + admin usage dashboard.
     */
    public static function costCentsSince(CarbonInterface $since, ?int $teamId = null): int
    {
        return (int) static::query()
            ->where('created_at', '>=', $since)
            ->when($teamId, fn ($q) => $q->where('team_id', $teamId))
            ->sum('cost_cents');
    }
}
