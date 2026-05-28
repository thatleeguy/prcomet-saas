<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\DemoRequest;
use App\Models\PublicationItem;
use App\Models\Team;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Top-line "is the platform alive?" stats for the admin dashboard.
 * Adoption and supply-side health, not match quality (that's the next widget).
 */
class PlatformOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeTeams = Team::where('is_active', true)->count();
        $pendingTeams = Team::where('is_active', false)->count();

        $companies = Company::where('is_active', true)->count();

        $itemsToday = PublicationItem::whereDate('analyzed_at', today())->count();
        $itemsWeek = PublicationItem::where('analyzed_at', '>=', now()->subDays(7))->count();

        $newRequests = DemoRequest::where('status', DemoRequest::STATUS_NEW)->count();

        return [
            Stat::make('Active teams', $activeTeams)
                ->description($pendingTeams > 0 ? "{$pendingTeams} pending activation" : 'No teams pending')
                ->descriptionIcon($pendingTeams > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->color($pendingTeams > 0 ? 'warning' : 'success'),

            Stat::make('Companies monitored', $companies)
                ->description('Active across all teams')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),

            Stat::make('Items analyzed', $itemsWeek)
                ->description("{$itemsToday} today")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary'),

            Stat::make('Demo requests', $newRequests)
                ->description($newRequests === 0 ? 'Inbox clear' : 'Awaiting reply')
                ->descriptionIcon($newRequests > 0 ? 'heroicon-m-inbox-arrow-down' : 'heroicon-m-check')
                ->color($newRequests > 0 ? 'warning' : 'success'),
        ];
    }
}
