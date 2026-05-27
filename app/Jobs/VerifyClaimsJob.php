<?php

namespace App\Jobs;

use App\Models\ExtractedClaim;
use App\Services\PriceData\PriceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Walk unverified claims with timeframes that have elapsed and decide whether
 * the prediction was correct, given commodity-price data.
 *
 * Coarse v0 logic: parse a $/oz-style predicted_outcome for the claim's topic
 * commodity, compare with the actual spot at the timeframe end date, mark
 * correct / incorrect / partial / unverifiable. Anything we can't parse stays
 * unverifiable and is shown in the brief without a track-record badge.
 *
 * Real product will use the LLM itself to evaluate fuzzy outcomes ("supply
 * tightened materially") against narrative evidence. Not in v0.
 */
class VerifyClaimsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $claimId) {}

    public function handle(PriceProvider $prices): void
    {
        $claim = ExtractedClaim::find($this->claimId);
        if (! $claim || $claim->verified_outcome !== null) {
            return;
        }

        $outcome = $this->verify($claim, $prices);

        $claim->forceFill([
            'verified_outcome' => $outcome['outcome'],
            'verified_at' => now(),
            'verification_evidence' => $outcome['evidence'],
        ])->save();
    }

    /** @return array{outcome:string, evidence:array} */
    private function verify(ExtractedClaim $claim, PriceProvider $prices): array
    {
        if (! $claim->topic || ! $claim->predicted_outcome || ! $claim->timeframe) {
            return ['outcome' => ExtractedClaim::VERIFIED_UNVERIFIABLE, 'evidence' => ['reason' => 'missing fields']];
        }

        $resolveBy = $this->parseTimeframe($claim->timeframe);
        if (! $resolveBy || $resolveBy->isFuture()) {
            return ['outcome' => ExtractedClaim::VERIFIED_UNVERIFIABLE, 'evidence' => ['reason' => 'timeframe not parseable or not yet elapsed']];
        }

        $targetPrice = $this->parsePrice($claim->predicted_outcome);
        if ($targetPrice === null) {
            return ['outcome' => ExtractedClaim::VERIFIED_UNVERIFIABLE, 'evidence' => ['reason' => 'predicted_outcome did not parse as a price']];
        }

        $actual = $prices->priceAt($claim->topic, $resolveBy);
        if ($actual === null) {
            return ['outcome' => ExtractedClaim::VERIFIED_UNVERIFIABLE, 'evidence' => ['reason' => "no price data for {$claim->topic} on {$resolveBy->toDateString()}"]];
        }

        $direction = strtolower($claim->predicted_outcome);
        $isUnder = str_contains($direction, 'under') || str_contains($direction, 'below') || str_contains($direction, 'down');
        $isOver = str_contains($direction, 'over') || str_contains($direction, 'above') || str_contains($direction, 'up');

        $evidence = [
            'commodity' => $claim->topic,
            'date' => $resolveBy->toDateString(),
            'target' => $targetPrice,
            'actual' => $actual,
        ];

        if ($isUnder) {
            return ['outcome' => $actual < $targetPrice ? ExtractedClaim::VERIFIED_CORRECT : ExtractedClaim::VERIFIED_INCORRECT, 'evidence' => $evidence];
        }
        if ($isOver) {
            return ['outcome' => $actual > $targetPrice ? ExtractedClaim::VERIFIED_CORRECT : ExtractedClaim::VERIFIED_INCORRECT, 'evidence' => $evidence];
        }

        // Equality-ish: within 5% counts as correct, within 10% partial, else incorrect.
        $delta = abs($actual - $targetPrice) / $targetPrice;
        $outcome = match (true) {
            $delta <= 0.05 => ExtractedClaim::VERIFIED_CORRECT,
            $delta <= 0.10 => ExtractedClaim::VERIFIED_PARTIAL,
            default => ExtractedClaim::VERIFIED_INCORRECT,
        };

        return ['outcome' => $outcome, 'evidence' => $evidence + ['delta_pct' => round($delta * 100, 2)]];
    }

    private function parseTimeframe(string $raw): ?CarbonImmutable
    {
        try {
            return CarbonImmutable::parse($raw);
        } catch (\Throwable) {
            // common forms like "Q1 2026" / "by year-end 2026" — best effort
            if (preg_match('/Q([1-4])\s+(\d{4})/', $raw, $m)) {
                $quarterEndMonth = [1 => 3, 2 => 6, 3 => 9, 4 => 12][(int) $m[1]];
                return CarbonImmutable::create((int) $m[2], $quarterEndMonth)->endOfMonth();
            }
            if (preg_match('/year[-\s]?end\s+(\d{4})/i', $raw, $m)) {
                return CarbonImmutable::create((int) $m[1], 12, 31);
            }
            return null;
        }
    }

    private function parsePrice(string $raw): ?float
    {
        if (preg_match('/\$?\s*([0-9][0-9,]*(?:\.[0-9]+)?)/', $raw, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }
        return null;
    }
}
