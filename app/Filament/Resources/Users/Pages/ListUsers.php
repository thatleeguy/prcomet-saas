<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    /** No header create action — users sign themselves up. */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
