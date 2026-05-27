<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'bio' => $this->faker->paragraph(),
            'x_handle' => '@'.$this->faker->userName(),
            'email' => $this->faker->safeEmail(),
            'website' => 'https://'.$this->faker->domainName(),
            'tags' => [],
        ];
    }
}
