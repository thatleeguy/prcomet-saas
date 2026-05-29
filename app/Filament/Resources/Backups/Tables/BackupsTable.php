<?php

namespace App\Filament\Resources\Backups\Tables;

use App\Models\Backup;
use App\Services\Backup\BackupService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BackupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('filename')
                    ->label('Archive')
                    ->searchable()
                    ->formatStateUsing(fn (string $state) => basename($state))
                    ->description(fn (Backup $r) => $r->filename)
                    ->weight('medium'),
                TextColumn::make('db_driver')
                    ->label('Driver')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mysql' => 'warning',
                        'sqlite' => 'success',
                        default => 'gray',
                    }),
                IconColumn::make('includes_files')
                    ->label('Files')
                    ->boolean()
                    ->trueIcon('heroicon-o-photo')
                    ->trueColor('primary')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),
                TextColumn::make('humanSize')
                    ->label('Size')
                    ->state(fn (Backup $r) => $r->humanSize())
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('size_bytes', $direction)),
                TextColumn::make('createdBy.name')
                    ->label('By')
                    ->placeholder('System')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('db_driver')
                    ->label('Driver')
                    ->options([
                        'sqlite' => 'SQLite',
                        'mysql' => 'MySQL',
                    ]),
            ])
            ->recordActions([
                // Download streams the archive straight from the configured
                // backup disk via Storage::download().
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn (Backup $r) => $r->exists())
                    ->action(fn (Backup $r) => $r->disk()->download(
                        $r->filename,
                        basename($r->filename),
                    )),
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Removes the archive from disk and the metadata row. Cannot be undone.')
                    ->action(function (Backup $r) {
                        app(BackupService::class)->delete($r);
                        Notification::make()->title('Backup deleted')->success()->send();
                    }),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Create backup')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->schema([
                        Toggle::make('include_files')
                            ->label('Include uploaded files')
                            ->default(true)
                            ->helperText('Bundles the public storage disk (media library, branding, one-pager assets). Skip for a DB-only snapshot if you only need the data.'),
                        Textarea::make('notes')
                            ->placeholder('e.g. pre-deploy snapshot before migration X')
                            ->rows(2)
                            ->maxLength(500),
                    ])
                    ->action(function (array $data) {
                        $backup = app(BackupService::class)->create(
                            createdByUserId: auth()->id(),
                            includeFiles: (bool) ($data['include_files'] ?? true),
                            notes: $data['notes'] ?? null,
                        );

                        Notification::make()
                            ->title('Backup created')
                            ->body("Saved {$backup->filename} ({$backup->humanSize()}).")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No backups yet')
            ->emptyStateDescription('Click "Create backup" to take the first snapshot. Each archive bundles the DB plus (optionally) the uploaded files.')
            ->emptyStateIcon('heroicon-o-archive-box');
    }
}
