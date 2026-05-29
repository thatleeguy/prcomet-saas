<?php

namespace App\Filament\Pages;

use App\Services\SystemHealthService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Operator-only system health dashboard.
 *
 * Shows the five checks the SystemHealthService computes (scheduler,
 * queue, database, storage, Anthropic), plus pending + failed job
 * counts and the latest failure messages. Read-only by default; two
 * header actions ("Retry all failed", "Wipe failures") sit behind
 * confirmation prompts for when the operator wants to act.
 */
class SystemHealth extends Page
{
    protected string $view = 'filament.pages.system-health';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $title = 'System health';

    protected static ?string $navigationLabel = 'System health';

    protected static ?int $navigationSort = 95;

    /** @var array<string, mixed> */
    public array $checks = [];

    /** @var array<int, array<string, mixed>> */
    public array $recentFailures = [];

    public int $pendingJobs = 0;

    public int $failedJobs = 0;

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $svc = app(SystemHealthService::class);
        $this->checks = $svc->checkAll();
        $this->pendingJobs = $svc->countPendingJobs();
        $this->failedJobs = $svc->countFailedJobs();
        $this->recentFailures = $svc->recentFailures();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->refresh()),

            Action::make('retryFailed')
                ->label('Retry all failed jobs')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('primary')
                ->visible(fn () => $this->failedJobs > 0)
                ->requiresConfirmation()
                ->modalDescription('Pushes every row in failed_jobs back onto the queue. Use this after you\'ve fixed the underlying cause.')
                ->action(function () {
                    Artisan::call('queue:retry', ['id' => ['all']]);
                    $this->refresh();
                    Notification::make()
                        ->title('Retry dispatched')
                        ->body(trim(Artisan::output()))
                        ->success()
                        ->send();
                }),

            Action::make('flushFailed')
                ->label('Wipe failed jobs table')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->visible(fn () => $this->failedJobs > 0)
                ->requiresConfirmation()
                ->modalDescription('Drops every row in failed_jobs. Use when the failures are old and irrelevant. Cannot be undone.')
                ->action(function () {
                    DB::table('failed_jobs')->delete();
                    $this->refresh();
                    Notification::make()->title('Failed jobs table wiped')->success()->send();
                }),
        ];
    }
}
