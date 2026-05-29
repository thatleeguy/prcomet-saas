<?php

namespace App\Services\Llm;

use App\Models\LlmUsageEvent;
use App\Models\Team;
use App\Models\User;
use App\Notifications\LlmBudgetAlertNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

/**
 * Central cost ledger + budget enforcer for LLM calls.
 *
 * Wraps four responsibilities so the AnthropicLlmClient can stay thin:
 *
 *   1. guardSystemBudget() — pre-flight check. Throws
 *      BudgetExceededException when the system-wide daily or weekly cap
 *      has been hit. Callers handle that as a soft failure.
 *
 *   2. record() — writes one LlmUsageEvent per call, costing the row
 *      against pricing from config/llm.php at the moment of write.
 *      Cost is never re-priced retrospectively.
 *
 *   3. dispatchSoftAlertsIfNeeded() — checks for two conditions that
 *      should fire an operator notification, and fires each at most
 *      once per window (cache-locked):
 *        - Per-team daily spend crossed team_daily_soft_cents.
 *        - System daily spend crossed system_approaching_pct of
 *          system_daily_cents.
 *
 *   4. snapshot() — read-only spend totals for the Filament dashboard.
 *
 * Nothing here is ever surfaced to end users. Notification recipients
 * default to every is_admin User, overridable via config/llm.php.
 */
class LlmUsageTracker
{
    public function guardSystemBudget(): void
    {
        $todaySpend = LlmUsageEvent::costCentsSince(today());
        $systemDaily = (int) config('llm.budgets.system_daily_cents');

        if ($systemDaily > 0 && $todaySpend >= $systemDaily) {
            throw new BudgetExceededException(
                window: 'daily',
                message: 'System daily LLM budget reached.',
            );
        }

        $weekStart = now()->startOfWeek();
        $weekSpend = LlmUsageEvent::costCentsSince($weekStart);
        $systemWeekly = (int) config('llm.budgets.system_weekly_cents');

        if ($systemWeekly > 0 && $weekSpend >= $systemWeekly) {
            throw new BudgetExceededException(
                window: 'weekly',
                message: 'System weekly LLM budget reached.',
            );
        }
    }

    public function record(
        string $model,
        int $inputTokens,
        int $outputTokens,
        string $feature,
        ?int $teamId = null,
        ?int $userId = null,
    ): LlmUsageEvent {
        $costCents = $this->costCentsFor($model, $inputTokens, $outputTokens);

        return LlmUsageEvent::create([
            'team_id' => $teamId,
            'user_id' => $userId,
            'feature' => $feature,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost_cents' => $costCents,
        ]);
    }

    /**
     * Look up the per-1M-token rates for the given model (or the
     * default fallback) and compute the total cost for this call.
     */
    public function costCentsFor(string $model, int $inputTokens, int $outputTokens): int
    {
        $rates = config("llm.pricing.{$model}") ?? config('llm.pricing.default');

        $inputCents = ($inputTokens * (int) $rates['input_cents_per_million']) / 1_000_000;
        $outputCents = ($outputTokens * (int) $rates['output_cents_per_million']) / 1_000_000;

        // ceil so we round up — under-charging ourselves on the ledger
        // would mean blowing through caps invisibly.
        return (int) ceil($inputCents + $outputCents);
    }

    /**
     * Fire operator notifications when thresholds are first crossed.
     * Both alerts are cache-locked for the relevant window so a noisy
     * day doesn't spam the operators.
     */
    public function dispatchSoftAlertsIfNeeded(?int $teamId = null): void
    {
        if ($teamId !== null) {
            $this->maybeSendTeamSoftAlert($teamId);
        }

        $this->maybeSendApproachingSystemCapAlert();
    }

    private function maybeSendTeamSoftAlert(int $teamId): void
    {
        $threshold = (int) config('llm.budgets.team_daily_soft_cents');
        if ($threshold <= 0) {
            return;
        }

        $spend = LlmUsageEvent::costCentsSince(today(), $teamId);
        if ($spend < $threshold) {
            return;
        }

        $key = "llm:alert:team:{$teamId}:".today()->toDateString();
        if (Cache::has($key)) {
            return;
        }

        // Lock the alert for the rest of the day. setting the value
        // first prevents a duplicate from sneaking in if two jobs race.
        Cache::put($key, true, now()->endOfDay());

        $team = Team::find($teamId);
        $this->notifyOperators(
            subject: 'Team approaching daily LLM spend',
            body: $team
                ? "Team \"{$team->name}\" has spent ".self::format($spend)." in LLM calls today, above the ".self::format($threshold)." soft cap. Not throttled — informational."
                : "Team id {$teamId} (since deleted) has spent ".self::format($spend)." today.",
        );
    }

    private function maybeSendApproachingSystemCapAlert(): void
    {
        $cap = (int) config('llm.budgets.system_daily_cents');
        $pct = (int) config('llm.budgets.system_approaching_pct');
        if ($cap <= 0 || $pct <= 0) {
            return;
        }

        $threshold = (int) (($cap * $pct) / 100);
        $spend = LlmUsageEvent::costCentsSince(today());
        if ($spend < $threshold) {
            return;
        }

        $key = 'llm:alert:system:'.today()->toDateString();
        if (Cache::has($key)) {
            return;
        }
        Cache::put($key, true, now()->endOfDay());

        $this->notifyOperators(
            subject: 'System approaching daily LLM cap',
            body: 'Daily LLM spend is at '.self::format($spend)." of the ".self::format($cap)." system cap. New calls will be blocked once the cap is reached.",
        );
    }

    private function notifyOperators(string $subject, string $body): void
    {
        $explicit = (array) config('llm.alert_recipients');

        if ($explicit !== []) {
            Notification::route('mail', $explicit)
                ->notify(new LlmBudgetAlertNotification($subject, $body));
            return;
        }

        $admins = User::where('is_admin', true)->get();
        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new LlmBudgetAlertNotification($subject, $body));
    }

    /**
     * Read-only spend snapshot for the operator dashboard. Bundles up
     * the four numbers the dashboard widget renders.
     *
     * @return array{
     *   today_cents:int,
     *   week_cents:int,
     *   system_daily_cap_cents:int,
     *   system_weekly_cap_cents:int,
     * }
     */
    public function snapshot(): array
    {
        return [
            'today_cents' => LlmUsageEvent::costCentsSince(today()),
            'week_cents' => LlmUsageEvent::costCentsSince(now()->startOfWeek()),
            'system_daily_cap_cents' => (int) config('llm.budgets.system_daily_cents'),
            'system_weekly_cap_cents' => (int) config('llm.budgets.system_weekly_cents'),
        ];
    }

    private static function format(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}
