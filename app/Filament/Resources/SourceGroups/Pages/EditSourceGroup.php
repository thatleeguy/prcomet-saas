<?php

namespace App\Filament\Resources\SourceGroups\Pages;

use App\Filament\Resources\SourceGroups\SourceGroupResource;
use App\Models\SourceGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSourceGroup extends EditRecord
{
    protected static string $resource = SourceGroupResource::class;

    /**
     * Catalogue deletion lives only on the edit page — the table-level
     * actions were removed to make the operator commit to a single
     * record before retiring it. Trash (soft-delete) is the default;
     * force delete sits beside it for irreversible cleanup, and
     * restore is exposed for trashed records.
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Trash catalogue')
                ->modalHeading(fn (SourceGroup $record) => "Trash \"{$record->name}\"?")
                ->modalDescription('Hides the catalogue from the operator pickers and stops new subscriptions. Existing team subscriptions and the sources tagged in this catalogue are left untouched. You can restore it from this page later.')
                ->modalSubmitActionLabel('Trash it'),

            ForceDeleteAction::make()
                ->label('Delete permanently')
                ->modalHeading(fn (SourceGroup $record) => "Permanently delete \"{$record->name}\"?")
                ->modalDescription('Drops the catalogue row, every team subscription to it, and every source ↔ catalogue pivot row. The underlying sources stay put. This cannot be undone.')
                ->modalSubmitActionLabel('Delete forever'),

            RestoreAction::make()
                ->modalHeading(fn (SourceGroup $record) => "Restore \"{$record->name}\"?")
                ->modalDescription('Brings the catalogue back. Teams that had it before they were trashed have to be re-subscribed — restoring the catalogue does not re-attach those pivot rows.'),
        ];
    }
}
