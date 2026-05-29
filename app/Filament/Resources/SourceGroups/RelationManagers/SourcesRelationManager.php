<?php

namespace App\Filament\Resources\SourceGroups\RelationManagers;

use App\Filament\Resources\Sources\Schemas\SourceForm;
use App\Models\Source;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * Manage the sources tagged into a catalogue from the SourceGroup
 * edit page.
 *
 * Two ways to add sources here:
 *   - Attach: pick from existing sources. Cheap, no row duplication —
 *     the same source can live in several catalogues simultaneously
 *     and ingest a single feed.
 *   - Create: spin up a brand-new source pre-tagged into this group.
 *
 * Detach removes the row from the pivot only; the source itself
 * stays put (still ingestable, still in any other groups it belongs
 * to). To retire a source entirely use the Source resource's trash
 * action.
 */
class SourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'sources';

    protected static ?string $title = 'Sources in this catalogue';

    public function form(Schema $schema): Schema
    {
        // Reuse the canonical SourceForm so a source created from here
        // has the same fields it would from the top-level resource.
        return SourceForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('semibold'),
                TextColumn::make('type')->badge(),
                TextColumn::make('sourceGroups.name')
                    ->label('Other catalogues')
                    ->badge()
                    ->color('gray')
                    // Only show the badges that aren't THIS catalogue —
                    // makes the "where else does this source live" check
                    // legible at a glance.
                    ->formatStateUsing(function ($state, $record) {
                        return $record->sourceGroups
                            ->where('id', '!=', $this->getOwnerRecord()->id)
                            ->pluck('name')
                            ->implode(', ') ?: '—';
                    }),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('last_ingested_at')->since()->label('Last ingest'),
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
                TernaryFilter::make('is_active'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attach existing source')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'feed_url', 'base_url'])
                    // No dedupe needed — the pivot's unique index stops a
                    // double-attach at the database level.
                    ->color('primary'),

                CreateAction::make()
                    ->label('Create new source')
                    ->using(function (array $data, $livewire) {
                        // CreateAction on a BelongsToMany would attach the
                        // new row via the pivot automatically, but we want
                        // to make the path explicit so the scope defaults
                        // to "global" and the operator can't accidentally
                        // create a private team source from here.
                        $data['scope'] = Source::SCOPE_GLOBAL;
                        $data['team_id'] = null;

                        $source = Source::create($data);
                        $this->getOwnerRecord()->sources()->attach($source->id);

                        return $source;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make()
                    ->label('Remove from catalogue')
                    ->requiresConfirmation()
                    ->modalDescription('This unpins the source from this catalogue. The source itself is untouched — it stays in any other catalogues it belongs to and keeps ingesting.'),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ])
            ->defaultSort('name');
    }
}
