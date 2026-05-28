<?php

namespace App\Filament\Resources\DemoRequests\Tables;

use App\Models\DemoRequest;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DemoRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('email')->searchable()->color('gray'),
                TextColumn::make('company')->searchable()->sortable()->weight('medium'),
                TextColumn::make('role')->color('gray')->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        DemoRequest::STATUS_NEW => 'warning',
                        DemoRequest::STATUS_CONTACTED => 'info',
                        DemoRequest::STATUS_SCHEDULED => 'primary',
                        DemoRequest::STATUS_CLOSED => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')->label('Submitted')->since()->sortable(),
                TextColumn::make('contacted_at')->label('Contacted')->since()->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    DemoRequest::STATUS_NEW => 'New',
                    DemoRequest::STATUS_CONTACTED => 'Contacted',
                    DemoRequest::STATUS_SCHEDULED => 'Scheduled',
                    DemoRequest::STATUS_CLOSED => 'Closed',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
