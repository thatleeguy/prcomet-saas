<?php

namespace App\Services\PriceData;

use Carbon\CarbonInterface;

/**
 * Commodity price lookup contract used by claim verification.
 *
 * v0 implementation is a stub — see {@see StubPriceProvider}. Production will
 * wire a real source (LBMA fixings, Kitco, a paid commodities API). Kept
 * minimal so we can swap providers without touching verification logic.
 */
interface PriceProvider
{
    /**
     * Closest available USD spot price for the given commodity slug on a date.
     * Returns null if the commodity is unknown or no data exists.
     */
    public function priceAt(string $commodity, CarbonInterface $date): ?float;
}
