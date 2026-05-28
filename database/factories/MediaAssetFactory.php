<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => MediaAsset::TYPE_IMAGE,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'file_path' => 'media/'.$this->faker->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => $this->faker->numberBetween(50_000, 2_000_000),
            'width_px' => $this->faker->numberBetween(800, 4000),
            'height_px' => $this->faker->numberBetween(600, 3000),
            'tags' => [],
            'is_active' => true,
            'source' => MediaAsset::SOURCE_MANUAL,
            'sort_order' => 0,
        ];
    }

    public function image(): static
    {
        return $this->state(['type' => MediaAsset::TYPE_IMAGE]);
    }

    public function logo(): static
    {
        return $this->state(['type' => MediaAsset::TYPE_LOGO]);
    }

    public function pdf(): static
    {
        return $this->state([
            'type' => MediaAsset::TYPE_PDF,
            'mime_type' => 'application/pdf',
            'file_path' => 'media/'.$this->faker->uuid().'.pdf',
        ]);
    }

    public function quote(string $text = null, string $attribution = null): static
    {
        return $this->state([
            'type' => MediaAsset::TYPE_QUOTE,
            'file_path' => null,
            'mime_type' => null,
            'size_bytes' => null,
            'width_px' => null,
            'height_px' => null,
            'quote_text' => $text ?? $this->faker->sentence(15),
            'quote_attribution' => $attribution ?? $this->faker->name().', CEO',
        ]);
    }

    public function link(): static
    {
        return $this->state([
            'type' => MediaAsset::TYPE_LINK,
            'file_path' => null,
            'mime_type' => null,
            'size_bytes' => null,
            'width_px' => null,
            'height_px' => null,
            'url' => $this->faker->url(),
        ]);
    }

    public function fromPressRelease(int $pressReleaseId): static
    {
        return $this->state([
            'source' => MediaAsset::SOURCE_PRESS_RELEASE,
            'source_press_release_id' => $pressReleaseId,
        ]);
    }
}
