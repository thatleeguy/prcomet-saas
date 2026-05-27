<?php

namespace App\Services\PriceData;

use Carbon\CarbonInterface;

/**
 * Test/dev price provider. Returns null for everything by default; tests can
 * programmatically set values via {@see set}. This lets verification jobs
 * run end-to-end without requiring a real commodity-price API.
 */
class StubPriceProvider implements PriceProvider
{
    /** @var array<string, array<string, float>> */
    private array $prices = [];

    public function set(string $commodity, CarbonInterface $date, float $usd): self
    {
        $this->prices[strtolower($commodity)][$date->toDateString()] = $usd;
        return $this;
    }

    public function priceAt(string $commodity, CarbonInterface $date): ?float
    {
        return $this->prices[strtolower($commodity)][$date->toDateString()] ?? null;
    }
}
