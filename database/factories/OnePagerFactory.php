<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\OnePager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OnePager>
 */
class OnePagerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid()->toString(),
            'match_id' => MatchRecord::factory(),
            'company_id' => Company::factory(),
            'created_by_id' => null,
            'note_md' => null,
            'status' => OnePager::STATUS_DRAFT,
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => OnePager::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }
}
