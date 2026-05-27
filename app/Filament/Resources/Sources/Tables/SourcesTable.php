<?php

namespace App\Filament\Resources\Sources\Tables;

use App\Jobs\IngestSourceJob;
use App\Models\Source;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->wrap(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('scope')->badge()
                    ->color(fn (string $state): string => $state === Source::SCOPE_GLOBAL ? 'success' : 'gray'),
                TextColumn::make('team.name')->label('Team')->toggleable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('last_ingested_at')->since()->sortable(),
                TextColumn::make('items_count')->counts('items')->label('Items'),
            ])
            ->filters([
                SelectFilter::make('type')->options([
                    Source::TYPE_PUBLICATION => 'Publication',
                    Source::TYPE_PODCAST => 'Podcast',
                    Source::TYPE_SUBSTACK => 'Substack',
                    Source::TYPE_X => 'X',
                    Source::TYPE_YOUTUBE => 'YouTube',
                ]),
                SelectFilter::make('scope')->options([
                    Source::SCOPE_GLOBAL => 'Global',
                    Source::SCOPE_TEAM => 'Team',
                ]),
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                Action::make('ingest')
                    ->label('Ingest now')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function (Source $record) {
                        dispatch_sync(new IngestSourceJob($record->id));

                        Notification::make()
                            ->title("Ingested {$record->name}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Source $record) => in_array($record->ingest_strategy, ['rss', 'atom'])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
