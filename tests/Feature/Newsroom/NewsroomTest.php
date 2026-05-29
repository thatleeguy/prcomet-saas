<?php

use App\Livewire\Newsroom\SubscribeForm;
use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\NewsroomSubscriber;
use App\Models\OnePager;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('auto-generates a slug from the company name on create', function () {
    $company = Company::factory()->create(['name' => 'Aurelian Gold Resources Corp']);
    expect($company->slug)->toBe('aurelian-gold-resources-corp');
});

it('disambiguates colliding slugs with a numeric suffix', function () {
    Company::factory()->create(['name' => 'Acme', 'slug' => 'acme']);
    $b = Company::factory()->create(['name' => 'Acme']);
    expect($b->slug)->toBe('acme-2');
});

it('returns 404 when the newsroom is not published', function () {
    $company = Company::factory()->create(['newsroom_published' => false]);

    $this->get('/newsroom/'.$company->slug)->assertNotFound();
});

it('renders the public newsroom page with the company brand', function () {
    $company = Company::factory()->create([
        'name' => 'Aurelian Gold',
        'newsroom_published' => true,
        'tagline' => 'Nevada-focused explorer.',
        'accent_color' => '#B8501D',
    ]);

    $this->get('/newsroom/'.$company->slug)
        ->assertOk()
        ->assertSee('Aurelian Gold')
        ->assertSee('Nevada-focused explorer.')
        ->assertSee('#B8501D', false); // accent CSS variable
});

it('lists published one-pagers (newest first) and hides unpublished ones', function () {
    $company = Company::factory()->create(['newsroom_published' => true]);

    $source = Source::factory()->create();
    $item = PublicationItem::factory()->create(['source_id' => $source->id, 'title' => 'Big drill result']);
    $release = PressRelease::factory()->create(['company_id' => $company->id]);
    $match = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'press_release_id' => $release->id,
        'publication_item_id' => $item->id,
    ]);

    OnePager::create([
        'company_id' => $company->id,
        'match_id' => $match->id,
        'title' => 'Published story',
        'status' => OnePager::STATUS_PUBLISHED,
        'published_at' => now()->subDays(2),
    ]);
    OnePager::create([
        'company_id' => $company->id,
        'match_id' => null,
        'title' => 'Draft story',
        'status' => OnePager::STATUS_DRAFT,
    ]);

    $this->get('/newsroom/'.$company->slug)
        ->assertOk()
        ->assertSee('Published story')
        ->assertDontSee('Draft story');
});

it('captures an email via the subscribe form', function () {
    $company = Company::factory()->create(['newsroom_published' => true]);

    Livewire::test(SubscribeForm::class, ['company' => $company])
        ->set('email', 'rob@example.com')
        ->call('subscribe')
        ->assertSet('submitted', true);

    expect(NewsroomSubscriber::count())->toBe(1);
    expect(NewsroomSubscriber::first()->email)->toBe('rob@example.com');
});

it('does not store duplicate subscriptions for the same (company, email)', function () {
    $company = Company::factory()->create(['newsroom_published' => true]);

    Livewire::test(SubscribeForm::class, ['company' => $company])
        ->set('email', 'rob@example.com')
        ->call('subscribe');

    Livewire::test(SubscribeForm::class, ['company' => $company])
        ->set('email', 'ROB@example.com') // case-insensitive uniqueness
        ->call('subscribe');

    expect(NewsroomSubscriber::count())->toBe(1);
});

it('rejects malformed emails on the subscribe form', function () {
    $company = Company::factory()->create(['newsroom_published' => true]);

    Livewire::test(SubscribeForm::class, ['company' => $company])
        ->set('email', 'not-an-email')
        ->call('subscribe')
        ->assertHasErrors(['email']);

    expect(NewsroomSubscriber::count())->toBe(0);
});
