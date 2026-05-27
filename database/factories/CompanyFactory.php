<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        $exchanges = ['TSX-V', 'TSX', 'ASX', 'CSE', 'LSE-AIM'];
        $commodities = ['gold', 'silver', 'copper', 'lithium', 'nickel', 'uranium'];

        return [
            'team_id' => Team::factory(),
            'name' => $this->faker->company().' Mining Corp',
            'ticker' => strtoupper($this->faker->lexify('????')),
            'exchange' => $this->faker->randomElement($exchanges),
            'website' => 'https://'.$this->faker->domainName(),
            'rss_feed_url' => 'https://'.$this->faker->domainName().'/news/rss.xml',
            'secondary_feeds' => [],
            'ir_contact_name' => $this->faker->name(),
            'ir_contact_email' => $this->faker->safeEmail(),
            'ir_contact_phone' => $this->faker->phoneNumber(),
            'sector_tags' => $this->faker->randomElements($commodities, 2),
            'is_active' => true,
        ];
    }
}
