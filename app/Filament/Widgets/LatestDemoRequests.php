<?php

namespace App\Filament\Widgets;

use App\Models\DemoRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Recent demo requests surfaced on the admin dashboard.
 * Tap a row to jump to the full DemoRequestResource for follow-up.
 */
class LatestDemoRequests extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent demo requests')
            ->description('Latest inbound from the landing page. New ones at the top.')
            ->query(fn (): Builder => DemoRequest::query()->latest()->limit(8))
            ->columns([
                TextColumn::make('name')->weight('medium'),
                TextColumn::make('email')->color('gray'),
                TextColumn::make('company')->weight('medium'),
                TextColumn::make('role')->color('gray')->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        DemoRequest::STATUS_NEW => 'warning',
                        DemoRequest::STATUS_CONTACTED => 'info',
                        DemoRequest::STATUS_SCHEDULED => 'primary',
                        DemoRequest::STATUS_CLOSED => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->since()->color('gray'),
            ])
            ->recordUrl(fn (DemoRequest $record): string => route('filament.admin.resources.demo-requests.edit', $record))
            ->emptyStateHeading('No demo requests yet')
            ->emptyStateDescription('When someone submits the landing-page form, they\'ll appear here.')
            ->paginated(false);
    }
}
