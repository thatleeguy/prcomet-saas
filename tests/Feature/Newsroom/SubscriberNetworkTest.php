<?php

use App\Events\OnePagerPublished;
use App\Livewire\Newsroom\SubscribeForm;
use App\Livewire\Subscriptions\Manage;
use App\Mail\InstantPublishNotice;
use App\Mail\SubscriptionAdded;
use App\Mail\SubscriptionConfirmRequest;
use App\Mail\SubscriptionDigest;
use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\NewsroomSubscriber;
use App\Models\NewsroomSubscription;
use App\Models\OnePager;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function publishedOnePager(Company $company, ?\Carbon\CarbonInterface $when = null): OnePager
{
    $source = Source::factory()->create();
    $item = PublicationItem::factory()->create(['source_id' => $source->id]);
    $release = PressRelease::factory()->create(['company_id' => $company->id]);
    $match = MatchRecord::factory()->create([
        'company_id' => $company->id,
        'press_release_id' => $release->id,
        'publication_item_id' => $item->id,
    ]);
    return OnePager::create([
        'company_id' => $company->id,
        'match_id' => $match->id,
        'title' => 'Story '.\Illuminate\Support\Str::random(4),
        'status' => OnePager::STATUS_PUBLISHED,
        'published_at' => $when ?? now(),
    ]);
}

it('upserts a single subscriber identity per email across companies', function () {
    Mail::fake();
    $a = Company::factory()->create(['newsroom_published' => true]);
    $b = Company::factory()->create(['newsroom_published' => true]);

    Livewire::test(SubscribeForm::class, ['company' => $a])
        ->set('email', 'rob@example.com')
        ->call('subscribe');

    Livewire::test(SubscribeForm::class, ['company' => $b])
        ->set('email', 'rob@example.com')
        ->call('subscribe');

    expect(NewsroomSubscriber::count())->toBe(1);
    $rob = NewsroomSubscriber::first();
    expect($rob->subscriptions()->count())->toBe(2);
});

it('sends a confirm email on first subscribe and an added email on follow-ups', function () {
    Mail::fake();
    $a = Company::factory()->create(['newsroom_published' => true]);
    $b = Company::factory()->create(['newsroom_published' => true]);

    // First time — confirm-request email + unconfirmed state.
    Livewire::test(SubscribeForm::class, ['company' => $a])
        ->set('email', 'rob@example.com')
        ->call('subscribe');
    Mail::assertQueued(SubscriptionConfirmRequest::class);

    // Confirm the subscriber so the next subscribe takes the
    // "added" path.
    $rob = NewsroomSubscriber::first();
    $rob->forceFill(['confirmed_at' => now()])->save();

    Livewire::test(SubscribeForm::class, ['company' => $b])
        ->set('email', 'rob@example.com')
        ->call('subscribe');
    Mail::assertQueued(SubscriptionAdded::class);
});

it('issues every subscriber a unique token', function () {
    Mail::fake();
    $c = Company::factory()->create(['newsroom_published' => true]);

    Livewire::test(SubscribeForm::class, ['company' => $c])
        ->set('email', 'a@example.com')->call('subscribe');
    Livewire::test(SubscribeForm::class, ['company' => $c])
        ->set('email', 'b@example.com')->call('subscribe');

    $tokens = NewsroomSubscriber::pluck('token');
    expect($tokens)->toHaveCount(2);
    expect($tokens->unique())->toHaveCount(2);
    expect(strlen($tokens->first()))->toBe(64);
});

it('confirm endpoint stamps confirmed_at and is idempotent', function () {
    $sub = NewsroomSubscriber::create(['email' => 'r@example.com']);

    $this->get(route('subscriptions.confirm', $sub->token))->assertOk()->assertSee('confirmed');
    expect($sub->fresh()->confirmed_at)->not->toBeNull();
    $stamp = $sub->fresh()->confirmed_at;

    // Re-hit — should not overwrite the existing confirmation.
    $this->get(route('subscriptions.confirm', $sub->token))->assertOk();
    expect($sub->fresh()->confirmed_at->timestamp)->toBe($stamp->timestamp);
});

it('global unsubscribe endpoint sets the flag and shows the goodbye page', function () {
    $sub = NewsroomSubscriber::create(['email' => 'r@example.com', 'confirmed_at' => now()]);

    $this->get(route('subscriptions.unsubscribe-all', $sub->token))->assertOk()->assertSee('Unsubscribed');
    expect($sub->fresh()->unsubscribed_at)->not->toBeNull();
});

it('manage page lists active subscriptions and lets you unsubscribe from one', function () {
    $sub = NewsroomSubscriber::create(['email' => 'r@example.com', 'confirmed_at' => now()]);
    $a = Company::factory()->create(['name' => 'Alpha', 'newsroom_published' => true]);
    $b = Company::factory()->create(['name' => 'Beta', 'newsroom_published' => true]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $sub->id, 'company_id' => $a->id, 'subscribed_at' => now()]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $sub->id, 'company_id' => $b->id, 'subscribed_at' => now()]);

    Livewire::test(Manage::class, ['token' => $sub->token])
        ->call('unsubscribeFrom', $a->id);

    expect(NewsroomSubscription::where('newsroom_subscriber_id', $sub->id)
        ->whereNull('unsubscribed_at')->count())->toBe(1);
});

it('manage page can subscribe to a discoverable company', function () {
    $sub = NewsroomSubscriber::create(['email' => 'r@example.com', 'confirmed_at' => now()]);
    $c = Company::factory()->create(['newsroom_published' => true]);

    Livewire::test(Manage::class, ['token' => $sub->token])
        ->call('subscribeTo', $c->id);

    expect(NewsroomSubscription::where('newsroom_subscriber_id', $sub->id)
        ->where('company_id', $c->id)->whereNull('unsubscribed_at')->count())->toBe(1);
});

it('manage page cadence change updates the subscriber row', function () {
    $sub = NewsroomSubscriber::create(['email' => 'r@example.com', 'confirmed_at' => now()]);

    Livewire::test(Manage::class, ['token' => $sub->token])
        ->set('cadence', 'instant');

    expect($sub->fresh()->cadence)->toBe('instant');
});

it('OnePagerPublished event fires when a one-pager is published', function () {
    Event::fake([OnePagerPublished::class]);

    $user = makeUserWithTeam(['is_active' => true]);
    $company = Company::factory()->create(['team_id' => $user->currentTeam->id]);
    $page = OnePager::create([
        'company_id' => $company->id,
        'match_id' => null,
        'status' => OnePager::STATUS_DRAFT,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\OnePagers\Edit::class, ['company' => $company, 'onePager' => $page])
        ->set('title', 'Anything')
        ->call('publish');

    Event::assertDispatched(OnePagerPublished::class);
});

it('instant-cadence subscribers receive InstantPublishNotice on publish', function () {
    Mail::fake();

    $company = Company::factory()->create(['newsroom_published' => true]);

    $instant = NewsroomSubscriber::create([
        'email' => 'i@example.com', 'cadence' => 'instant', 'confirmed_at' => now(),
    ]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $instant->id, 'company_id' => $company->id, 'subscribed_at' => now()]);

    $digest = NewsroomSubscriber::create([
        'email' => 'd@example.com', 'cadence' => 'weekly', 'confirmed_at' => now(),
    ]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $digest->id, 'company_id' => $company->id, 'subscribed_at' => now()]);

    $page = publishedOnePager($company);
    OnePagerPublished::dispatch($page);

    Mail::assertQueued(InstantPublishNotice::class, 1);
    Mail::assertQueued(InstantPublishNotice::class, fn ($mail) => $mail->subscriber->id === $instant->id);
});

it('digest job batches new publishes across all subscribed companies', function () {
    Mail::fake();

    $a = Company::factory()->create(['newsroom_published' => true, 'name' => 'Alpha']);
    $b = Company::factory()->create(['newsroom_published' => true, 'name' => 'Beta']);

    $sub = NewsroomSubscriber::create([
        'email' => 'r@example.com', 'cadence' => 'weekly',
        'confirmed_at' => now()->subDays(8),
        'last_digest_sent_at' => now()->subDays(8),
    ]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $sub->id, 'company_id' => $a->id, 'subscribed_at' => now()->subDays(8)]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $sub->id, 'company_id' => $b->id, 'subscribed_at' => now()->subDays(8)]);

    publishedOnePager($a, now()->subDays(2));
    publishedOnePager($b, now()->subDays(1));

    (new \App\Jobs\DispatchSubscriptionDigestsJob)->handle();

    Mail::assertQueued(SubscriptionDigest::class, 1);
    Mail::assertQueued(SubscriptionDigest::class, function ($mail) use ($sub) {
        return $mail->subscriber->id === $sub->id
            && $mail->groups->count() === 2;
    });

    expect($sub->fresh()->last_digest_sent_at)->not->toBeNull();
    expect($sub->fresh()->last_digest_sent_at->isAfter(now()->subMinute()))->toBeTrue();
});

it('digest job skips subscribers with no new publishes since last_digest_sent_at', function () {
    Mail::fake();

    $c = Company::factory()->create(['newsroom_published' => true]);
    $sub = NewsroomSubscriber::create([
        'email' => 'r@example.com', 'cadence' => 'weekly',
        'confirmed_at' => now()->subDays(8),
        'last_digest_sent_at' => now()->subHours(2), // recent
    ]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $sub->id, 'company_id' => $c->id, 'subscribed_at' => now()]);
    publishedOnePager($c, now()->subHour()); // newer than last_digest_sent_at, but not weekly-due

    (new \App\Jobs\DispatchSubscriptionDigestsJob)->handle();

    Mail::assertNothingQueued();
});

it('does not send to globally-unsubscribed identities even when due', function () {
    Mail::fake();

    $c = Company::factory()->create(['newsroom_published' => true]);
    $sub = NewsroomSubscriber::create([
        'email' => 'r@example.com', 'cadence' => 'weekly',
        'confirmed_at' => now()->subDays(8),
        'last_digest_sent_at' => now()->subDays(8),
        'unsubscribed_at' => now()->subHour(),
    ]);
    NewsroomSubscription::create(['newsroom_subscriber_id' => $sub->id, 'company_id' => $c->id, 'subscribed_at' => now()->subDays(8)]);
    publishedOnePager($c, now()->subDay());

    (new \App\Jobs\DispatchSubscriptionDigestsJob)->handle();

    Mail::assertNothingQueued();
});

it('co-branded From line is "Company via PrComet"', function () {
    $company = Company::factory()->create(['name' => 'Aurelian Gold']);
    $sub = NewsroomSubscriber::create(['email' => 'r@example.com', 'confirmed_at' => now()]);

    $mail = new InstantPublishNotice($sub, $company, publishedOnePager($company));
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toBe('Aurelian Gold via PrComet');
});

it('Reply-To routes to the press contact email when configured', function () {
    $company = Company::factory()->create([
        'name' => 'Aurelian',
        'press_contact_email' => 'press@aureliangold.example',
    ]);
    $sub = NewsroomSubscriber::create(['email' => 'r@example.com', 'confirmed_at' => now()]);

    $mail = new InstantPublishNotice($sub, $company, publishedOnePager($company));
    $envelope = $mail->envelope();

    expect($envelope->replyTo)->not->toBeEmpty();
    expect($envelope->replyTo[0]->address)->toBe('press@aureliangold.example');
});
