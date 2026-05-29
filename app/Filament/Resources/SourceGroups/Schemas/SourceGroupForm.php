<?php

namespace App\Filament\Resources\SourceGroups\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SourceGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $set, $get, $context) {
                        // Auto-slug only on create so renaming a live group
                        // doesn't silently change its visibility key.
                        if ($context === 'create' && filled($state) && blank($get('slug'))) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('URL-safe identifier. Lowercase letters, numbers, and hyphens.')
                    ->columnSpan(1),

                TextInput::make('icon_emoji')
                    ->label('Icon')
                    ->maxLength(8)
                    ->placeholder('⛏️')
                    ->helperText('A single emoji used in lists and cards.')
                    ->columnSpan(1),

                ColorPicker::make('accent_color')
                    ->label('Accent colour')
                    ->columnSpan(1),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Customer-visible explanation of what this catalogue covers.'),

                Toggle::make('is_active')
                    ->default(true)
                    ->helperText('Inactive catalogues are hidden from the operator picker but existing subscriptions keep working.')
                    ->columnSpan(1),

                Toggle::make('is_premium')
                    ->live()
                    ->helperText('Premium catalogues require an explicit subscription on the team record.')
                    ->columnSpan(1),

                TextInput::make('monthly_price_cents')
                    ->label('Monthly price (cents)')
                    ->numeric()
                    ->minValue(0)
                    ->visible(fn ($get) => (bool) $get('is_premium'))
                    ->helperText('Operator-only ledger of the price. Billing happens manually.')
                    ->columnSpan(1),
            ]);
    }
}
