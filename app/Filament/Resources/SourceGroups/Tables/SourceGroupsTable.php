<?php

namespace App\Filament\Resources\SourceGroups\Tables;

use App\Models\SourceGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SourceGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('icon_emoji')->label('')->size('lg'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (SourceGroup $r) => \Illuminate\Support\Str::limit($r->description, 80)),
                TextColumn::make('sources_count')
                    ->counts('sources')
                    ->label('Sources')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('teams_count')
                    ->counts('teams')
                    ->label('Subscribers')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean()
                    ->trueIcon('heroicon-o-sparkles')
                    ->falseIcon('heroicon-o-gift')
                    ->trueColor('warning')
                    ->falseColor('success'),
                TextColumn::make('priceLabel')
                    ->label('Price')
                    ->state(fn (SourceGroup $r) => $r->priceLabel()),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('deleted_at')
                    ->label('Trashed')
                    ->since()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_premium'),
                TernaryFilter::make('is_active'),
                TrashedFilter::make(),
            ])
            // Delete + restore + force-delete intentionally live on the
            // edit page only. Deleting a catalogue ripples across team
            // subscriptions and Source::visibleTo for every customer in
            // them, so the operator should be looking at the record
            // before they pull the trigger.
            // Edit stays visible on trashed records too — it's the
            // only way back into the page where the restore action
            // lives.
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('name');
    }
}
