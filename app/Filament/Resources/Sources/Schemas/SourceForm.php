<?php

namespace App\Filament\Resources\Sources\Schemas;

use App\Models\Source;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('scope')
                    ->required()
                    ->default(Source::SCOPE_GLOBAL)
                    ->options([
                        Source::SCOPE_GLOBAL => 'Global (all teams)',
                        Source::SCOPE_TEAM => 'Team (single team)',
                    ])
                    ->live(),

                Select::make('team_id')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->visible(fn ($get) => $get('scope') === Source::SCOPE_TEAM)
                    ->requiredIf('scope', Source::SCOPE_TEAM),

                Select::make('type')
                    ->required()
                    ->options([
                        Source::TYPE_PUBLICATION => 'Publication',
                        Source::TYPE_PODCAST => 'Podcast',
                        Source::TYPE_SUBSTACK => 'Substack / newsletter',
                        Source::TYPE_X => 'X (Twitter)',
                        Source::TYPE_YOUTUBE => 'YouTube',
                    ]),

                TextInput::make('name')->required()->columnSpanFull(),
                TextInput::make('base_url')->url()->label('Site URL'),
                TextInput::make('feed_url')->url()->label('Feed URL (RSS/Atom)'),

                Select::make('ingest_strategy')
                    ->required()
                    ->default('rss')
                    ->options([
                        'rss' => 'RSS / Atom',
                        'api' => 'API (manual integration)',
                        'manual' => 'Manual entry only',
                    ]),

                TagsInput::make('tags')
                    ->placeholder('gold, copper, macro')
                    ->columnSpanFull(),

                Toggle::make('is_active')->required()->default(true),
            ]);
    }
}
