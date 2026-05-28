<?php

namespace App\Filament\Widgets;

use App\Models\MatchRecord;
use Filament\Widgets\ChartWidget;

/**
 * Daily matches surfaced (bars) overlaid with daily placements (line).
 * If the surfaced volume holds steady but the placement line trends down,
 * something is decaying — prompt drift, source quality, or user trust.
 */
class MatchActivityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Match surfacing & placements';

    protected ?string $description = 'Daily output of the matching engine over the last 30 days.';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? '30');
        $start = now()->subDays($days - 1)->startOfDay();

        $surfaced = MatchRecord::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->pluck('c', 'day');

        $placed = MatchRecord::query()
            ->selectRaw('DATE(updated_at) as day, COUNT(*) as c')
            ->where('status', MatchRecord::STATUS_PLACED)
            ->where('updated_at', '>=', $start)
            ->groupBy('day')
            ->pluck('c', 'day');

        $labels = [];
        $surfacedSeries = [];
        $placedSeries = [];

        $cursor = $start->copy();
        for ($i = 0; $i < $days; $i++) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('M j');
            $surfacedSeries[] = (int) ($surfaced[$key] ?? 0);
            $placedSeries[] = (int) ($placed[$key] ?? 0);
            $cursor->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Surfaced',
                    'data' => $surfacedSeries,
                    'backgroundColor' => 'rgba(67, 57, 220, 0.20)',
                    'borderColor' => 'rgba(67, 57, 220, 0.9)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Placed',
                    'data' => $placedSeries,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.20)',
                    'borderColor' => 'rgba(16, 185, 129, 0.9)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top', 'align' => 'end'],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
