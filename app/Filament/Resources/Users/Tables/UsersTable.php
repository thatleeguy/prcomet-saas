<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('email')->searchable()->color('gray'),
                TextColumn::make('currentTeam.name')
                    ->label('Current team')
                    ->placeholder('—')
                    ->color('gray')
                    ->toggleable(),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Signed up')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_admin')->label('Super-admin'),
                TernaryFilter::make('email_verified_at')
                    ->label('Verified')
                    ->nullable()
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('email_verified_at'),
                        false: fn ($q) => $q->whereNull('email_verified_at'),
                    ),
            ])
            ->recordActions([
                Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => "Impersonate {$record->name}?")
                    ->modalDescription('You\'ll be signed in as them and see exactly what they see. A banner at the top of every page lets you return to your admin account.')
                    ->modalSubmitActionLabel('Impersonate')
                    ->visible(fn (User $record) => $record->id !== auth()->id())
                    ->action(function (User $record) {
                        session()->put('impersonator_id', auth()->id());
                        auth()->login($record);
                    })
                    ->successRedirectUrl('/dashboard'),

                Action::make('sendPasswordReset')
                    ->label('Send password reset')
                    ->icon('heroicon-o-key')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => "Send reset link to {$record->email}?")
                    ->modalDescription('They\'ll receive an email with a link to set a new password.')
                    ->action(function (User $record) {
                        Password::sendResetLink(['email' => $record->email]);

                        Notification::make()
                            ->title('Reset link sent')
                            ->body("A password reset link was emailed to {$record->email}.")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
