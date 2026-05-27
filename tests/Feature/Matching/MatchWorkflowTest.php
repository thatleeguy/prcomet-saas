<?php

use App\Livewire\Matches;
use App\Models\Company;
use App\Models\MatchEvent;
use App\Models\MatchRecord;
use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('lists matches for the current team only', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $ours = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'rationale_md' => 'Our team match',
    ]);
    $foreign = MatchRecord::factory()->create([
        'rationale_md' => 'Other team match',
    ]);

    Livewire::actingAs($user)
        ->test(Matches\Index::class)
        ->assertSee('Our team match')
        ->assertDontSee('Other team match');
});

it('filters matches by status', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $new = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'status' => MatchRecord::STATUS_NEW,
        'rationale_md' => 'Match new-status',
    ]);
    $saved = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'status' => MatchRecord::STATUS_SAVED,
        'rationale_md' => 'Match saved-status',
    ]);

    Livewire::actingAs($user)
        ->test(Matches\Index::class)
        ->assertSee('Match new-status')
        ->assertDontSee('Match saved-status')
        ->call('setStatus', 'saved')
        ->assertSee('Match saved-status')
        ->assertDontSee('Match new-status');
});

it('updates match status and logs an event', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $match = MatchRecord::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($user)
        ->test(Matches\Show::class, ['match' => $match])
        ->call('updateStatus', 'contacted');

    expect($match->fresh()->status)->toBe(MatchRecord::STATUS_CONTACTED);

    $event = MatchEvent::first();
    expect($event->event_type)->toBe(MatchRecord::STATUS_CONTACTED)
        ->and($event->user_id)->toBe($user->id);
});

it('adds a note as a match event', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $match = MatchRecord::factory()->create(['company_id' => $company->id]);

    Livewire::actingAs($user)
        ->test(Matches\Show::class, ['match' => $match])
        ->set('note', 'Pitched on Tuesday')
        ->call('addNote');

    $event = MatchEvent::first();
    expect($event->event_type)->toBe(MatchEvent::TYPE_NOTE)
        ->and($event->notes_md)->toBe('Pitched on Tuesday');
});

it('forbids viewing another team\'s match', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $foreign = MatchRecord::factory()->create();

    actingAs($user)
        ->get(route('matches.show', $foreign))
        ->assertForbidden();
});

it('ignores invalid status transitions silently', function () {
    $user = makeUserWithTeam(['is_active' => true, 'max_companies' => 5]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $match = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'status' => MatchRecord::STATUS_NEW,
    ]);

    Livewire::actingAs($user)
        ->test(Matches\Show::class, ['match' => $match])
        ->call('updateStatus', 'made-up-status');

    expect($match->fresh()->status)->toBe(MatchRecord::STATUS_NEW)
        ->and(MatchEvent::count())->toBe(0);
});
