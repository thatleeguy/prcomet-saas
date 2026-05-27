<?php

namespace App\Filament\Resources\Teams\Tables;

use App\Models\Team;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable(),

                TextColumn::make('owner.email')
                    ->label('Owner email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('max_companies')
                    ->label('Seats')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('activated_at')
                    ->label('Activated')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('paid_through_at')
                    ->label('Paid through')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Signed up')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All teams')
                    ->trueLabel('Active only')
                    ->falseLabel('Pending / suspended'),
            ])
            ->recordActions([
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Team $record) => ! $record->is_active)
                    ->schema([
                        TextInput::make('max_companies')
                            ->label('Seat limit (companies)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->default(fn (Team $record) => max(1, $record->max_companies)),
                    ])
                    ->action(function (Team $record, array $data) {
                        $record->activate(
                            seats: (int) $data['max_companies'],
                            admin: auth()->user(),
                        );

                        Notification::make()
                            ->title("Activated {$record->name}")
                            ->body("Seat limit set to {$data['max_companies']}.")
                            ->success()
                            ->send();
                    }),

                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Team $record) => $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading(fn (Team $record) => "Suspend {$record->name}?")
                    ->modalDescription('The team will lose workspace access. Their data is preserved and they can be re-activated later.')
                    ->action(function (Team $record) {
                        $record->suspend();

                        Notification::make()
                            ->title("Suspended {$record->name}")
                            ->warning()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Billing'),
            ])
            ->toolbarActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
