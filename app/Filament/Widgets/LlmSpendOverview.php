<?php

namespace App\Filament\Widgets;

use App\Models\LlmUsageEvent;
use App\Services\Llm\LlmUsageTracker;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Operator-only LLM cost dial.
 *
 * Surfaces the four numbers an operator wants on the dashboard:
 *  - Today's spend / system daily cap (with pct + colour escalation)
 *  - Week's spend / system weekly cap
 *  - Today's call count by feature (match brief, watch confirmation)
 *  - The top-spending team today (so we know who to call if costs spike)
 *
 * Read-only; this widget never throttles or modifies anything. The
 * throttle logic lives in LlmUsageTracker::guardSystemBudget().
 */
class LlmSpendOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected function getStats(): array
    {
        $snap = app(LlmUsageTracker::class)->snapshot();

        $today = $snap['today_cents'];
        $week = $snap['week_cents'];
        $dailyCap = $snap['system_daily_cap_cents'];
        $weeklyCap = $snap['system_weekly_cap_cents'];

        $dailyPct = $dailyCap > 0 ? (int) round(($today * 100) / $dailyCap) : 0;
        $weeklyPct = $weeklyCap > 0 ? (int) round(($week * 100) / $weeklyCap) : 0;

        // Today's call counts split by feature so the operator can see
        // whether briefs or watch confirmations are driving spend.
        $todayByFeature = LlmUsageEvent::query()
            ->where('created_at', '>=', today())
            ->selectRaw('feature, COUNT(*) as c, SUM(cost_cents) as s')
            ->groupBy('feature')
            ->pluck('s', 'feature')
            ->toArray();

        $briefCents = (int) ($todayByFeature[LlmUsageEvent::FEATURE_MATCH_BRIEF] ?? 0);
        $watchCents = (int) ($todayByFeature[LlmUsageEvent::FEATURE_WATCH_HIT_CONFIRM] ?? 0);

        $topTeamRow = LlmUsageEvent::query()
            ->where('created_at', '>=', today())
            ->whereNotNull('team_id')
            ->selectRaw('team_id, SUM(cost_cents) as s')
            ->groupBy('team_id')
            ->orderByDesc('s')
            ->first();

        $topTeamLabel = '—';
        if ($topTeamRow) {
            $team = \App\Models\Team::find($topTeamRow->team_id);
            $topTeamLabel = ($team?->name ?? 'Team #'.$topTeamRow->team_id)
                .' · '.self::format((int) $topTeamRow->s);
        }

        return [
            Stat::make('LLM spend today', self::format($today))
                ->description($dailyCap > 0 ? "{$dailyPct}% of ".self::format($dailyCap).' daily cap' : 'No cap set')
                ->descriptionIcon($dailyPct >= 100 ? 'heroicon-m-no-symbol' : ($dailyPct >= 80 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-bolt'))
                ->color($dailyPct >= 100 ? 'danger' : ($dailyPct >= 80 ? 'warning' : 'success')),

            Stat::make('LLM spend this week', self::format($week))
                ->description($weeklyCap > 0 ? "{$weeklyPct}% of ".self::format($weeklyCap).' weekly cap' : 'No cap set')
                ->descriptionIcon($weeklyPct >= 100 ? 'heroicon-m-no-symbol' : 'heroicon-m-calendar-days')
                ->color($weeklyPct >= 100 ? 'danger' : ($weeklyPct >= 80 ? 'warning' : 'success')),

            Stat::make('Today by feature', self::format($briefCents + $watchCents))
                ->description('Briefs '.self::format($briefCents).' · Watches '.self::format($watchCents))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Top team today', $topTeamLabel)
                ->description('Per-team daily spend leader')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),
        ];
    }

    private static function format(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}
