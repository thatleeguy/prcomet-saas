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
});

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

        Route::get('/companies', Livewire\Companies\Index::class)->name('companies.index');
        Route::get('/companies/create', Livewire\Companies\Edit::class)->name('companies.create');
        Route::get('/companies/{company}', Livewire\Companies\Show::class)->name('companies.show');
        Route::get('/companies/{company}/edit', Livewire\Companies\Edit::class)->name('companies.edit');
        Route::get('/companies/{company}/matches', Livewire\Matches\Index::class)->name('companies.matches');

        Route::get('/matches', Livewire\Matches\Index::class)->name('matches.index');
        Route::get('/matches/{match}', Livewire\Matches\Show::class)->name('matches.show');

        Route::get('/wins', Livewire\Wins\Index::class)->name('wins.index');

        Route::get('/stream', Livewire\Stream\Index::class)->name('stream.index');
        Route::get('/authors/{author:slug}', Livewire\Authors\Show::class)->name('authors.show');

        Route::get('/settings/digest', Livewire\Settings\DigestPreferences::class)->name('settings.digest');
    });
});
