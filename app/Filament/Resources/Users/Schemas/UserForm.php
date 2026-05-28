<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(120),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true),

                Toggle::make('is_admin')
                    ->label('Super-admin')
                    ->helperText('Grants access to the /admin panel. Use sparingly.'),

                Select::make('digest_frequency')
                    ->label('Digest cadence')
                    ->options([
                        User::DIGEST_DAILY => 'Daily',
                        User::DIGEST_WEEKLY => 'Weekly',
                        User::DIGEST_OFF => 'Off',
                    ])
                    ->default(User::DIGEST_DAILY)
                    ->required(),
            ]);
    }
}
