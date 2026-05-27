<?php

namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Source>
 */
class SourceFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'scope' => Source::SCOPE_GLOBAL,
            'team_id' => null,
            'type' => Source::TYPE_PUBLICATION,
            'name' => $name,
            'base_url' => 'https://'.$this->faker->domainName(),
            'feed_url' => 'https://'.$this->faker->domainName().'/feed.xml',
            'ingest_strategy' => 'rss',
            'ingest_config' => [],
            'tags' => $this->faker->randomElements(['gold', 'silver', 'copper', 'lithium', 'mining'], 2),
            'is_active' => true,
        ];
    }

    public function team(\App\Models\Team $team): static
    {
        return $this->state(fn () => [
            'scope' => Source::SCOPE_TEAM,
            'team_id' => $team->id,
        ]);
    }
}
