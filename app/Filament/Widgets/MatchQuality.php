<?php

namespace App\Filament\Widgets;

use App\Models\MatchRecord;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * The "are we good at our job?" widget. Matters more than adoption — if the
 * platform grows but matches are bad, churn follows.
 *
 * Key calibration check: avg score of PLACED matches should be measurably
 * higher than avg score of DISMISSED matches. If those numbers drift toward
 * each other, the score is losing predictive power and the prompt needs work.
 */
class MatchQuality extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Match quality';

    protected ?string $description = 'How successful we are at surfacing matches teams actually use.';

    protected function getStats(): array
    {
        // Look at the trailing 30 days so transient blips don't show up as quality issues.
        $base = MatchRecord::query()->where('created_at', '>=', now()->subDays(30));

        $total = (clone $base)->count();
        $dismissed = (clone $base)->where('status', MatchRecord::STATUS_DISMISSED)->count();
        $contacted = (clone $base)->where('status', MatchRecord::STATUS_CONTACTED)->count();
        $placed = (clone $base)->where('status', MatchRecord::STATUS_PLACED)->count();

        $dismissalRate = $total > 0 ? round(($dismissed / $total) * 100) : 0;
        $conversionRate = ($contacted + $placed) > 0
            ? round(($placed / ($contacted + $placed)) * 100)
            : 0;

        $avgPlaced = (clone $base)->where('status', MatchRecord::STATUS_PLACED)->avg('score');
        $avgDismissed = (clone $base)->where('status', MatchRecord::STATUS_DISMISSED)->avg('score');
        $scoreDelta = ($avgPlaced && $avgDismissed) ? ($avgPlaced - $avgDismissed) : null;

        return [
            // Dismissal rate. <30% is healthy; >50% means the LLM is surfacing
            // too many false positives and we need to look at prompt + threshold.
            Stat::make('Dismissal rate', $dismissalRate.'%')
                ->description($dismissalRate <= 30 ? 'Healthy' : ($dismissalRate <= 50 ? 'Watch' : 'Investigate'))
                ->descriptionIcon($dismissalRate <= 30 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($dismissalRate <= 30 ? 'success' : ($dismissalRate <= 50 ? 'warning' : 'danger')),

            // Outreach → placement conversion. The headline customer success number.
            Stat::make('Placement conversion', $conversionRate.'%')
                ->description($placed.' placed of '.($placed + $contacted).' in outreach')
                ->descriptionIcon('heroicon-m-trophy')
                ->color($conversionRate >= 30 ? 'success' : ($conversionRate >= 15 ? 'primary' : 'warning')),

            // Calibration check: how well does the score predict outcomes?
            Stat::make('Avg score · placed', $avgPlaced ? number_format($avgPlaced * 100).'%' : '—')
                ->description($avgDismissed ? 'vs '.number_format($avgDismissed * 100).'% on dismissed' : 'No comparator yet')
                ->descriptionIcon($scoreDelta !== null && $scoreDelta > 0.1 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
                ->color($scoreDelta !== null && $scoreDelta > 0.1 ? 'success' : 'warning'),

            // Throughput. Slow drying-up is worth knowing about.
            Stat::make('Matches surfaced', $total)
                ->description('Past 30 days')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('primary'),
        ];
    }
}
