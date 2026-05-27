<?php

use App\Jobs\IngestCompanyRssJob;
use App\Jobs\IngestSourceJob;
use App\Jobs\SendMatchDigestsJob;
use App\Models\Company;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled jobs
|--------------------------------------------------------------------------
*/

// Poll every active company's RSS feed once an hour. Jobs queue independently
// so a slow feed doesn't block the others.
Schedule::call(function () {
    Company::query()
        ->whereNotNull('rss_feed_url')
        ->where('is_active', true)
        ->whereHas('team', fn ($q) => $q->where('is_active', true))
        ->each(fn (Company $c) => IngestCompanyRssJob::dispatch($c->id));
})->hourly()->name('ingest-company-feeds')->withoutOverlapping();

// Corpus side — every 30 minutes. Cheap on the parser, expensive on LLM
// downstream, so we'll later tune per-source frequency by activity level.
Schedule::call(function () {
    Source::query()
        ->where('is_active', true)
        ->whereIn('ingest_strategy', ['rss', 'atom'])
        ->whereNotNull('feed_url')
        ->each(fn (Source $s) => IngestSourceJob::dispatch($s->id));
})->everyThirtyMinutes()->name('ingest-sources')->withoutOverlapping();

// Match digests — daily at 13:00 UTC (≈ 9am ET / 6am PT — tweak by user
// timezone post-launch). Each user's job decides based on their cadence.
Schedule::call(function () {
    User::query()
        ->where('digest_frequency', '!=', User::DIGEST_OFF)
        ->whereNotNull('current_team_id')
        ->whereHas('currentTeam', fn ($q) => $q->where('is_active', true))
        ->each(function (User $u) {
            $due = match ($u->digest_frequency) {
                User::DIGEST_DAILY => $u->digest_sent_at === null || $u->digest_sent_at->lt(now()->subHours(23)),
                User::DIGEST_WEEKLY => $u->digest_sent_at === null || $u->digest_sent_at->lt(now()->subDays(6)),
                default => false,
            };
            if ($due) {
                SendMatchDigestsJob::dispatch($u->id);
            }
        });
})->dailyAt('13:00')->name('send-match-digests')->withoutOverlapping();
