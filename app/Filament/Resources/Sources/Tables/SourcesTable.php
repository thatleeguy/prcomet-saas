<?php

namespace App\Filament\Resources\Sources\Tables;

use App\Jobs\IngestSourceJob;
use App\Models\Source;
use App\Models\SourceGroup;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->wrap(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('sourceGroups.name')
                    ->label('Catalogues')
                    ->badge()
                    ->color('primary')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('scope')->badge()
                    ->color(fn (string $state): string => $state === Source::SCOPE_GLOBAL ? 'success' : 'gray'),
                TextColumn::make('team.name')->label('Team')->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('last_ingested_at')->since()->sortable(),
                TextColumn::make('items_count')->counts('items')->label('Items'),
            ])
            ->filters([
                SelectFilter::make('sourceGroups')
                    ->label('Catalogue')
                    ->relationship('sourceGroups', 'name')
                    ->preload(),
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
                // Toggles the soft-delete scope; defaults to "without trashed"
                // so retired sources stay out of the way unless asked for.
                TrashedFilter::make(),
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
                    ->visible(fn (Source $record) => in_array($record->ingest_strategy, ['rss', 'atom']) && ! $record->trashed()),
                EditAction::make()->visible(fn (Source $record) => ! $record->trashed()),
                // Trash (soft delete) for live rows; restore for trashed ones.
                DeleteAction::make()->visible(fn (Source $record) => ! $record->trashed()),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    // Force delete is intentionally separate — purges the
                    // row and cascades publication items + matches.
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
