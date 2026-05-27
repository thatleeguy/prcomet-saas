<?php

namespace Database\Factories;

use App\Models\DemoRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DemoRequest>
 */
class DemoRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'company' => $this->faker->company(),
            'role' => $this->faker->jobTitle(),
            'website' => 'https://'.$this->faker->domainName(),
            'notes' => $this->faker->optional(0.4)->sentence(),
            'status' => DemoRequest::STATUS_NEW,
        ];
    }
}
