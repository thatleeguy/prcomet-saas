<?php

namespace App\Filament\Resources\Teams\Pages;

use App\Filament\Resources\Teams\TeamResource;
use Filament\Resources\Pages\ListRecords;

class ListTeams extends ListRecords
{
    protected static string $resource = TeamResource::class;

    /**
     * No create action — teams are created by user signup, not by admins.
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
