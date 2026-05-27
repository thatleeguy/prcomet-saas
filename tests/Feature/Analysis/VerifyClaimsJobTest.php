<?php

use App\Jobs\VerifyClaimsJob;
use App\Models\ExtractedClaim;
use App\Models\PublicationItem;
use App\Services\PriceData\PriceProvider;
use App\Services\PriceData\StubPriceProvider;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->prices = new StubPriceProvider;
    $this->app->instance(PriceProvider::class, $this->prices);
});

it('marks a directional prediction correct when actual stays below target', function () {
    $item = PublicationItem::factory()->create();
    $claim = ExtractedClaim::create([
        'publication_item_id' => $item->id,
        'claim_text' => 'Silver under $30 by Q1 2026',
        'topic' => 'silver',
        'predicted_outcome' => 'under $30',
        'timeframe' => 'Q1 2026',
    ]);

    $this->prices->set('silver', CarbonImmutable::create(2026, 3, 31), 24.50);

    dispatch_sync(new VerifyClaimsJob($claim->id));

    expect($claim->fresh()->verified_outcome)->toBe(ExtractedClaim::VERIFIED_CORRECT);
});

it('marks the prediction incorrect when actual exceeds target', function () {
    $item = PublicationItem::factory()->create();
    $claim = ExtractedClaim::create([
        'publication_item_id' => $item->id,
        'claim_text' => 'Silver under $30 by Q1 2026',
        'topic' => 'silver',
        'predicted_outcome' => 'under $30',
        'timeframe' => 'Q1 2026',
    ]);

    $this->prices->set('silver', CarbonImmutable::create(2026, 3, 31), 35.10);

    dispatch_sync(new VerifyClaimsJob($claim->id));

    expect($claim->fresh()->verified_outcome)->toBe(ExtractedClaim::VERIFIED_INCORRECT);
});

it('marks the prediction unverifiable when timeframe is in the future', function () {
    $item = PublicationItem::factory()->create();
    $claim = ExtractedClaim::create([
        'publication_item_id' => $item->id,
        'claim_text' => 'Copper above $5 by end-2030',
        'topic' => 'copper',
        'predicted_outcome' => 'above $5',
        'timeframe' => 'year-end 2030',
    ]);

    dispatch_sync(new VerifyClaimsJob($claim->id));

    expect($claim->fresh()->verified_outcome)->toBe(ExtractedClaim::VERIFIED_UNVERIFIABLE);
});

it('does not re-verify an already verified claim', function () {
    $item = PublicationItem::factory()->create();
    $claim = ExtractedClaim::create([
        'publication_item_id' => $item->id,
        'claim_text' => 'x',
        'topic' => 'gold',
        'predicted_outcome' => 'under $1000',
        'timeframe' => '2020-01-01',
        'verified_outcome' => ExtractedClaim::VERIFIED_INCORRECT,
        'verified_at' => now()->subDay(),
    ]);

    $verifiedAt = $claim->verified_at;
    dispatch_sync(new VerifyClaimsJob($claim->id));

    expect($claim->fresh()->verified_outcome)->toBe(ExtractedClaim::VERIFIED_INCORRECT)
        ->and($claim->fresh()->verified_at->toIso8601String())->toBe($verifiedAt->toIso8601String());
});
