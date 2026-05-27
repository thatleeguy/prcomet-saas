<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\PressRelease;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PressRelease>
 */
class PressReleaseFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence(8);
        $bodyText = $this->faker->paragraphs(4, true);

        return [
            'company_id' => Company::factory(),
            'external_guid' => 'guid-'.Str::random(16),
            'source_url' => 'https://example.com/news/'.Str::slug($title),
            'title' => $title,
            'body_html' => '<p>'.nl2br($bodyText).'</p>',
            'body_text' => $bodyText,
            'published_at' => now()->subDays($this->faker->numberBetween(0, 30)),
            'analysis_status' => PressRelease::ANALYSIS_PENDING,
        ];
    }
}
