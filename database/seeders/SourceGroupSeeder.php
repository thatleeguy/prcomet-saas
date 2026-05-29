<?php

namespace Database\Seeders;

use App\Models\Source;
use App\Models\SourceGroup;
use App\Models\Team;
use Illuminate\Database\Seeder;

/**
 * Bootstrap the catalogue layer and reshape existing sources into it.
 *
 * Creates four named catalogues — one free (the mining default), three
 * premium (oil & gas, clean energy, travel). The "Mining Publications"
 * catalogue inherits every global source the install already has, so
 * upgrading an existing install doesn't strand the team's corpus.
 *
 * Every existing active team gets auto-subscribed to the free Mining
 * catalogue. Premium catalogues seed with a few illustrative sources
 * each so the operator UI isn't empty.
 *
 *   php artisan db:seed --class=SourceGroupSeeder
 */
class SourceGroupSeeder extends Seeder
{
    public function run(): void
    {
        $mining = SourceGroup::updateOrCreate(
            ['slug' => 'mining-publications'],
            [
                'name' => 'Mining Publications',
                'description' => 'Trade press, newsletters, podcasts, and analyst desks covering the global mining sector. The default catalogue for junior mining customers.',
                'is_premium' => false,
                'is_active' => true,
                'icon_emoji' => '⛏️',
                'accent_color' => '#b45309',
            ]
        );

        $oilGas = SourceGroup::updateOrCreate(
            ['slug' => 'oil-gas-publications'],
            [
                'name' => 'Oil & Gas Publications',
                'description' => 'Upstream, midstream, and downstream coverage. Useful for E&P companies, oilfield services, and energy macro stories.',
                'is_premium' => true,
                'is_active' => true,
                'monthly_price_cents' => 9900,
                'icon_emoji' => '🛢️',
                'accent_color' => '#0f172a',
            ]
        );

        $cleanEnergy = SourceGroup::updateOrCreate(
            ['slug' => 'clean-energy'],
            [
                'name' => 'Clean Energy',
                'description' => 'Renewables, storage, hydrogen, and the energy transition. Critical-minerals and battery-metals coverage overlaps with Mining.',
                'is_premium' => true,
                'is_active' => true,
                'monthly_price_cents' => 9900,
                'icon_emoji' => '⚡',
                'accent_color' => '#059669',
            ]
        );

        $travel = SourceGroup::updateOrCreate(
            ['slug' => 'travel-publications'],
            [
                'name' => 'Travel Publications',
                'description' => 'Consumer and trade travel media. For hospitality, airlines, destination marketing, and travel-tech customers.',
                'is_premium' => true,
                'is_active' => true,
                'monthly_price_cents' => 4900,
                'icon_emoji' => '✈️',
                'accent_color' => '#0284c7',
            ]
        );

        // Pin every existing un-grouped global source into the Mining
        // catalogue so the team-visible corpus survives the migration.
        Source::query()
            ->where('scope', Source::SCOPE_GLOBAL)
            ->whereNull('source_group_id')
            ->update(['source_group_id' => $mining->id]);

        // Seed a few representative sources into each premium catalogue
        // so the operator UI shows real content out of the box.
        $this->seedSources($oilGas, [
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Reuters Energy', 'feed_url' => 'https://www.reuters.com/business/energy/feed', 'tags' => ['oil', 'gas', 'energy']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Oil & Gas Journal', 'feed_url' => 'https://www.ogj.com/feed', 'tags' => ['oil', 'gas']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Rigzone', 'feed_url' => 'https://www.rigzone.com/news/rss/', 'tags' => ['oil', 'gas', 'services']],
            ['type' => Source::TYPE_PODCAST, 'name' => 'Energy Mix Podcast', 'feed_url' => 'https://example.com/energymix.rss', 'tags' => ['oil', 'gas', 'podcast']],
        ]);

        $this->seedSources($cleanEnergy, [
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'PV Magazine', 'feed_url' => 'https://www.pv-magazine.com/feed/', 'tags' => ['solar', 'clean-energy']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'CleanTechnica', 'feed_url' => 'https://cleantechnica.com/feed/', 'tags' => ['cleantech', 'evs']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Greentech Media', 'feed_url' => 'https://www.greentechmedia.com/feed', 'tags' => ['clean-energy']],
            ['type' => Source::TYPE_SUBSTACK, 'name' => 'Heatmap News', 'feed_url' => 'https://heatmap.news/feed', 'tags' => ['climate', 'transition']],
        ]);

        $this->seedSources($travel, [
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Skift', 'feed_url' => 'https://skift.com/feed/', 'tags' => ['travel-trade']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'PhocusWire', 'feed_url' => 'https://www.phocuswire.com/rss', 'tags' => ['travel-tech']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Travel Weekly', 'feed_url' => 'https://www.travelweekly.com/RSS-Feeds', 'tags' => ['travel-trade']],
        ]);

        // Auto-subscribe every existing active team to the free mining
        // catalogue so the migration isn't silent.
        $teamIds = Team::where('is_active', true)->pluck('id');
        foreach ($teamIds as $teamId) {
            $mining->teams()->syncWithoutDetaching([
                $teamId => [
                    'is_complimentary' => false,
                    'subscribed_at' => now(),
                ],
            ]);
        }
    }

    private function seedSources(SourceGroup $group, array $rows): void
    {
        foreach ($rows as $row) {
            Source::updateOrCreate(
                ['scope' => Source::SCOPE_GLOBAL, 'name' => $row['name']],
                array_merge($row, [
                    'scope' => Source::SCOPE_GLOBAL,
                    'team_id' => null,
                    'source_group_id' => $group->id,
                    'ingest_strategy' => 'rss',
                    'is_active' => true,
                    'base_url' => $row['base_url'] ?? null,
                    'tags' => $row['tags'] ?? [],
                ])
            );
        }
    }
}
