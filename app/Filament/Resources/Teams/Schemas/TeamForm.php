<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TeamForm
{
    /**
     * The admin's edit form for a team. Scoped to operator-relevant fields —
     * `name` (read for context), `max_companies`, billing notes, paid_through.
     *
     * Activation itself is an action on the row/page, not a form toggle, so it
     * can capture activated_by_id and emit notifications cleanly.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Team name is set by the team owner and read-only here.'),

                TextInput::make('max_companies')
                    ->label('Seat limit (companies)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Maximum number of monitored companies this team can create.'),

                Textarea::make('billing_notes')
                    ->rows(4)
                    ->columnSpanFull()
                    ->helperText('Free-form notes about the billing relationship — invoice numbers, contract terms, anything operator-relevant.'),

                DateTimePicker::make('paid_through_at')
                    ->label('Paid through')
                    ->helperText('Optional. Used as a soft reminder; does not auto-suspend.'),

                Toggle::make('llm_observatory_enabled')
                    ->label('Observatory LLM confirmation')
                    ->helperText('Unlocks the literal+LLM matching mode on watches. Per-hit cost; toggle when an account upgrades.'),
            ]);
    }
}
