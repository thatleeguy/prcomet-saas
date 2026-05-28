<?php

namespace App\Filament\Resources\DemoRequests\Schemas;

use App\Models\DemoRequest;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DemoRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(120)->disabled()->dehydrated(false)
                    ->helperText('Submitted by the requester; not editable.'),
                TextInput::make('email')->label('Email')->email()->required()->disabled()->dehydrated(false),
                TextInput::make('company')->required()->disabled()->dehydrated(false),
                TextInput::make('role')->disabled()->dehydrated(false),
                TextInput::make('website')->url()->disabled()->dehydrated(false)->columnSpanFull(),
                Textarea::make('notes')->rows(4)->disabled()->dehydrated(false)->columnSpanFull()
                    ->placeholder('No additional notes submitted.'),

                Select::make('status')
                    ->required()
                    ->default(DemoRequest::STATUS_NEW)
                    ->options([
                        DemoRequest::STATUS_NEW => 'New — needs review',
                        DemoRequest::STATUS_CONTACTED => 'Contacted',
                        DemoRequest::STATUS_SCHEDULED => 'Demo scheduled',
                        DemoRequest::STATUS_CLOSED => 'Closed',
                    ])
                    ->helperText('Move through the funnel as you follow up.'),

                DateTimePicker::make('contacted_at')
                    ->label('Contacted at')
                    ->helperText('Auto-stamped when you mark contacted; you can override.'),
            ]);
    }
}
