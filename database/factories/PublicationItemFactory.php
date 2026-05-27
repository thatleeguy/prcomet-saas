<?php

namespace Database\Factories;

use App\Models\PublicationItem;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PublicationItem>
 */
class PublicationItemFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence(8);

        return [
            'source_id' => Source::factory(),
            'author_id' => null,
            'external_guid' => 'item-'.Str::random(16),
            'url' => 'https://example.com/articles/'.Str::slug($title),
            'title' => $title,
            'body_text' => $this->faker->paragraphs(4, true),
            'transcript' => null,
            'published_at' => now()->subDays($this->faker->numberBetween(0, 60)),
            'analysis_status' => PublicationItem::ANALYSIS_PENDING,
        ];
    }
}
