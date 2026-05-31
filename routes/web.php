<?php

use App\Livewire;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketing
|--------------------------------------------------------------------------
| Root-level routes are reserved for unauthenticated/marketing pages.
| Anything app-internal lives under /dashboard/* below.
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public one-pager. UUID-keyed; published-only renders. Tracking happens in the controller.
Route::get('/onepagers/{uuid}', [\App\Http\Controllers\OnePagerController::class, 'show'])
    ->name('onepagers.show');

// Public newsroom — permanent directory of a company's published one-pagers.
// Slug-keyed; 404s when newsroom_published is false.
Route::get('/newsroom/{company}', [\App\Http\Controllers\NewsroomController::class, 'show'])
    ->name('newsroom.show');

// Public articles — SEO-first marketing/education blog. The /topic/ route is
// declared before the slug route so "topic" isn't captured as an article slug.
Route::get('/articles', [\App\Http\Controllers\ArticleController::class, 'index'])
    ->name('articles.index');
Route::get('/articles/topic/{topic}', [\App\Http\Controllers\ArticleController::class, 'topic'])
    ->name('articles.topic');
Route::get('/articles/{article}', [\App\Http\Controllers\ArticleController::class, 'show'])
    ->name('articles.show');

// SEO crawl surfaces.
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])
    ->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\SitemapController::class, 'robots'])
    ->name('robots');

// Subscriber self-manage surface. Token-based, no password — Substack style.
// Token reveals subscription preferences only for that one identity.
Route::get('/subscriptions/confirm/{token}', [\App\Http\Controllers\SubscriptionsController::class, 'confirm'])
    ->name('subscriptions.confirm');
Route::get('/subscriptions/unsubscribe/{token}', [\App\Http\Controllers\SubscriptionsController::class, 'unsubscribeAll'])
    ->name('subscriptions.unsubscribe-all');
Route::get('/subscriptions/{token}', \App\Livewire\Subscriptions\Manage::class)
    ->name('subscriptions.manage');

// Stop impersonating. Available to any authenticated user since the
// impersonated user is the one signed in here; controller verifies state.
Route::post('/impersonate/stop', [\App\Http\Controllers\ImpersonationController::class, 'stop'])
    ->middleware('auth')
    ->name('impersonate.stop');

/*
|--------------------------------------------------------------------------
| Workspace
|--------------------------------------------------------------------------
| All authenticated app surface lives under /dashboard. The pending-activation
| page is the only authenticated route NOT gated by team.active, so a user
| with an unactivated team has somewhere to land.
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('dashboard')->group(function () {
    // Visible to authenticated users whose team has not yet been activated.
    Route::view('/pending', 'teams.pending')->name('teams.pending');

    // Active-team gated workspace.
    Route::middleware('team.active')->group(function () {
        Route::get('/', Livewire\Dashboard::class)->name('dashboard');

        // Pin a company as the user's "current focus" — drives the
        // workspace scoping (matches list, dashboard widget, nav items).
        Route::put('/current-company', [\App\Http\Controllers\CurrentCompanyController::class, 'update'])
            ->name('current-company.update');

        Route::get('/companies', Livewire\Companies\Index::class)->name('companies.index');
        Route::get('/companies/create', Livewire\Companies\Edit::class)->name('companies.create');
        Route::get('/companies/{company}', Livewire\Companies\Show::class)->name('companies.show');
        Route::get('/companies/{company}/edit', Livewire\Companies\Edit::class)->name('companies.edit');
        Route::get('/companies/{company}/matches', Livewire\Matches\Index::class)->name('companies.matches');
        Route::get('/companies/{company}/library', Livewire\Companies\MediaLibrary::class)->name('companies.library');
        Route::get('/companies/{company}/library/create', Livewire\Companies\MediaAssetEdit::class)->name('companies.library.create');
        Route::get('/companies/{company}/library/{asset}/edit', Livewire\Companies\MediaAssetEdit::class)->name('companies.library.edit');
        Route::get('/companies/{company}/onepagers', Livewire\OnePagers\Index::class)->name('companies.onepagers');
        // Create a standalone one-pager (no match) — the controller stamps
        // a draft row and redirects into the editor. POST so a refresh
        // doesn't spawn dupes.
        Route::post('/companies/{company}/onepagers', [\App\Http\Controllers\OnePagerCreateController::class, 'store'])
            ->name('companies.onepagers.store');
        // UUID-keyed edit URL — works for both standalone and match-bound
        // pages. The component scopes by company_id at mount.
        Route::get('/companies/{company}/onepagers/{onePager:uuid}', Livewire\OnePagers\Edit::class)
            ->name('companies.onepagers.edit');

        // Observatory — watches + hits feed.
        Route::get('/companies/{company}/observatory', Livewire\Observatory\Index::class)->name('companies.observatory');
        Route::get('/companies/{company}/observatory/create', Livewire\Observatory\Edit::class)->name('companies.observatory.create');
        Route::get('/companies/{company}/observatory/{watch}', Livewire\Observatory\Show::class)->name('companies.observatory.show');
        Route::get('/companies/{company}/observatory/{watch}/edit', Livewire\Observatory\Edit::class)->name('companies.observatory.edit');
        Route::get('/companies/{company}/branding', Livewire\Companies\Branding::class)->name('companies.branding');
        Route::get('/companies/{company}/audience', Livewire\Companies\Audience::class)->name('companies.audience');

        Route::get('/matches', Livewire\Matches\Index::class)->name('matches.index');
        Route::get('/matches/{match}', Livewire\Matches\Show::class)->name('matches.show');
        Route::get('/matches/{match}/one-pager', Livewire\OnePagers\Edit::class)->name('matches.one-pager');

        Route::get('/wins', Livewire\Wins\Index::class)->name('wins.index');

        Route::get('/stream', Livewire\Stream\Index::class)->name('stream.index');
        Route::get('/authors/{author:slug}', Livewire\Authors\Show::class)->name('authors.show');

        Route::get('/settings/digest', Livewire\Settings\DigestPreferences::class)->name('settings.digest');
    });
});
