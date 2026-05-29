<?php

namespace App\Filament\Resources\Teams\RelationManagers;

use App\Models\SourceGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Inline catalogue subscription manager on the Team edit page.
 *
 * Operators attach / detach catalogues from this panel. Each attachment
 * stamps a row in source_group_subscriptions carrying the provenance
 * fields (complimentary flag, subscribed_at, expires_at, who granted
 * it, free-form notes). Detaching is a clean row delete — visibility
 * stops immediately and the team's stream chips refresh on next view.
 *
 * Premium catalogues show a badge in the table so the operator can
 * tell at a glance which subscriptions carry real revenue.
 */
class SourceGroupSubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sourceGroups';

    protected static ?string $title = 'Catalogue subscriptions';

    protected static ?string $modelLabel = 'subscription';

    protected static ?string $pluralModelLabel = 'subscriptions';

    /**
     * Pivot-row form. Reused by both Attach (initial grant) and Edit
     * (changing terms after the fact).
     */
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('is_complimentary')
                ->label('Complimentary access')
                ->helperText('Free trial, loaner, or anything you do not intend to bill for.')
                ->default(false)
                ->columnSpan(1),

            DateTimePicker::make('subscribed_at')
                ->label('Subscribed at')
                ->default(now())
                ->seconds(false)
                ->columnSpan(1),

            DateTimePicker::make('expires_at')
                ->label('Expires at (optional)')
                ->seconds(false)
                ->helperText('Leave blank for open-ended access. Source::visibleTo respects this automatically.')
                ->columnSpan(1),

            TextInput::make('notes')
                ->label('Internal notes')
                ->maxLength(255)
                ->placeholder('e.g. "Q3 trial — convert by Sep 30"')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('icon_emoji')->label('')->size('lg'),
                TextColumn::make('name')
                    ->weight('semibold')
                    ->description(fn (SourceGroup $r) => \Illuminate\Support\Str::limit($r->description, 80)),
                TextColumn::make('sources_count')
                    ->counts('sources')
                    ->label('Sources')
                    ->badge()
                    ->color('primary'),
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
                IconColumn::make('pivot.is_complimentary')
                    ->label('Comp')
                    ->boolean()
                    ->trueIcon('heroicon-o-gift')
                    ->trueColor('info')
                    ->falseIcon('heroicon-o-minus'),
                TextColumn::make('pivot.subscribed_at')
                    ->label('Since')
                    ->since()
                    ->placeholder('—'),
                TextColumn::make('pivot.expires_at')
                    ->label('Expires')
                    ->dateTime('M j, Y')
                    ->placeholder('—')
                    ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : null),
            ])
            ->filters([
                TernaryFilter::make('is_premium')
                    ->label('Premium only'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Add catalogue')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn ($query) => $query->where('is_active', true))
                    ->recordSelectSearchColumns(['name', 'slug'])
                    ->schema(fn (AttachAction $action) => [
                        $action->getRecordSelect(),
                        Toggle::make('is_complimentary')
                            ->label('Complimentary access')
                            ->default(false),
                        DateTimePicker::make('subscribed_at')
                            ->default(now())
                            ->seconds(false),
                        DateTimePicker::make('expires_at')
                            ->label('Expires at (optional)')
                            ->seconds(false),
                        TextInput::make('notes')
                            ->maxLength(255),
                    ])
                    // Stamp who granted the access for the audit trail.
                    ->mutateFormDataUsing(function (array $data) {
                        $data['granted_by_user_id'] = auth()->id();
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make()
                    ->label('Remove')
                    ->requiresConfirmation()
                    ->modalDescription('The team will lose visibility of every source in this catalogue immediately. Their existing matches are unaffected.'),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ])
            ->defaultSort('source_groups.name');
    }
}
