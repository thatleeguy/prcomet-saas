<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchRecord>
 */
class MatchRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'press_release_id' => PressRelease::factory(),
            'publication_item_id' => PublicationItem::factory(),
            'author_id' => null,
            'score' => $this->faker->randomFloat(2, 0.6, 0.95),
            'rationale_md' => $this->faker->paragraph(),
            'suggested_angle_md' => $this->faker->paragraph(),
            'citations' => [],
            'status' => MatchRecord::STATUS_NEW,
        ];
    }
}
