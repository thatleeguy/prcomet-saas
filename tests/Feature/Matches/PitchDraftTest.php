<?php

use App\Livewire\Matches\Show;
use App\Models\Author;
use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\PitchDraft;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Stub the LLM with a deterministic JSON response so the prompt
    // tests don't burn a real API call.
    $fake = new FakeLlmClient;
    $fake->callback(fn () => json_encode([
        'subject' => 'Headline-driven test subject',
        'body' => "Para 1\n\nPara 2\n\nPara 3",
    ]));
    $this->app->instance(LlmClient::class, $fake);
});

function makeMatchForPitch(bool $teamHasUpgrade = true): MatchRecord
{
    $user = makeUserWithTeam(['is_active' => true, 'llm_observatory_enabled' => $teamHasUpgrade]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id, 'ir_contact_name' => 'Sarah Chen']);
    test()->actingAs($user);

    $author = Author::factory()->create(['name' => 'Robert Sinclair', 'tags' => ['gold', 'nevada']]);
    $source = Source::factory()->create(['name' => 'Mining.com']);
    $item = PublicationItem::factory()->create([
        'source_id' => $source->id,
        'author_id' => $author->id,
        'title' => 'Nevada drill season heats up',
    ]);
    $release = PressRelease::factory()->create([
        'company_id' => $company->id,
        'title' => 'Big Sky intercept',
        'body_text' => '12.4 g/t Au over 28m at Big Sky.',
    ]);
    return MatchRecord::factory()->create([
        'company_id' => $company->id,
        'press_release_id' => $release->id,
        'publication_item_id' => $item->id,
        'author_id' => $author->id,
    ]);
}

it('generates a draft and stores it under the chosen tone', function () {
    $match = makeMatchForPitch();

    Livewire::test(Show::class, ['match' => $match])
        ->set('pitchTone', PitchDraft::TONE_DIRECT)
        ->call('generatePitchDraft');

    $draft = PitchDraft::firstWhere('match_id', $match->id);
    expect($draft)->not->toBeNull();
    expect($draft->tone)->toBe('direct');
    expect($draft->subject)->toContain('test subject');
    expect($draft->body)->toContain('Para 1');
});

it('upserts on (match, tone) so re-running the same tone overwrites in place', function () {
    $match = makeMatchForPitch();

    Livewire::test(Show::class, ['match' => $match])
        ->call('generatePitchDraft');

    Livewire::test(Show::class, ['match' => $match])
        ->call('generatePitchDraft');

    expect(PitchDraft::where('match_id', $match->id)->count())->toBe(1);
});

it('stores separate rows for different tones on the same match', function () {
    $match = makeMatchForPitch();

    Livewire::test(Show::class, ['match' => $match])
        ->set('pitchTone', 'formal')
        ->call('generatePitchDraft')
        ->set('pitchTone', 'direct')
        ->call('generatePitchDraft')
        ->set('pitchTone', 'casual')
        ->call('generatePitchDraft');

    expect(PitchDraft::where('match_id', $match->id)->count())->toBe(3);
});

it('refuses to draft when the team is not entitled', function () {
    $match = makeMatchForPitch(teamHasUpgrade: false);

    Livewire::test(Show::class, ['match' => $match])
        ->call('generatePitchDraft');

    expect(PitchDraft::count())->toBe(0);
});

it('builds a mailto: URL with subject and body pre-filled', function () {
    $draft = new PitchDraft([
        'subject' => 'Test subject',
        'body' => "Line 1\nLine 2",
    ]);

    $url = $draft->mailtoUrl('rob@example.com');
    expect($url)->toStartWith('mailto:rob%40example.com?');
    expect($url)->toContain('subject=Test%20subject');
    expect($url)->toContain('body=Line%201');
});

it('handles a no-recipient mailto without breaking the URL', function () {
    $draft = new PitchDraft(['subject' => 'X', 'body' => 'Y']);
    $url = $draft->mailtoUrl();
    expect($url)->toStartWith('mailto:?');
});
