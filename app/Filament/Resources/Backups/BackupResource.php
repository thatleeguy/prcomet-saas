<?php

namespace App\Filament\Resources\Backups;

use App\Filament\Resources\Backups\Pages\ListBackups;
use App\Filament\Resources\Backups\Tables\BackupsTable;
use App\Models\Backup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Operator backup manager.
 *
 * Read-only resource — no create/edit forms because backups are
 * spawned by a service-backed header action, not free-form. The list
 * page shows every archive, lets the operator trigger a new one,
 * download an existing one, or delete a stale one.
 */
class BackupResource extends Resource
{
    protected static ?string $model = Backup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Backups';

    protected static ?string $modelLabel = 'Backup';

    protected static ?int $navigationSort = 90;

    public static function table(Table $table): Table
    {
        return BackupsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false; // creation goes through the header action
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBackups::route('/'),
        ];
    }
}
