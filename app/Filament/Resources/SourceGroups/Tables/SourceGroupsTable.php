<?php

namespace App\Filament\Resources\SourceGroups\Tables;

use App\Models\SourceGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
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
            ->recordActions([
                EditAction::make()->visible(fn (SourceGroup $r) => ! $r->trashed()),
                DeleteAction::make()->visible(fn (SourceGroup $r) => ! $r->trashed()),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
