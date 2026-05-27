<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

/**
 * Seed the global mining-vertical corpus.
 *
 * Real production deployment will use a larger curated set (~500-2,000) and
 * keep it under operator control via Filament. This seed gets a dev/staging
 * environment to a usable demo state. Feed URLs are best-effort representative
 * — verify each before pushing to production.
 *
 *   php artisan db:seed --class=MiningSourceSeeder
 */
class MiningSourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            // Mining trade press
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Mining.com', 'base_url' => 'https://www.mining.com', 'feed_url' => 'https://www.mining.com/feed/', 'tags' => ['general', 'mining']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'The Northern Miner', 'base_url' => 'https://www.northernminer.com', 'feed_url' => 'https://www.northernminer.com/feed/', 'tags' => ['general', 'mining']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Kitco News', 'base_url' => 'https://www.kitco.com', 'feed_url' => 'https://www.kitco.com/rss/KitcoNews.xml', 'tags' => ['gold', 'silver', 'precious-metals']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Resource World', 'base_url' => 'https://resourceworld.com', 'feed_url' => 'https://resourceworld.com/feed/', 'tags' => ['general', 'mining']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Junior Mining Network', 'base_url' => 'https://www.juniorminingnetwork.com', 'feed_url' => 'https://www.juniorminingnetwork.com/rss-news.xml', 'tags' => ['junior', 'mining']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Streetwise Reports', 'base_url' => 'https://www.streetwisereports.com', 'feed_url' => 'https://www.streetwisereports.com/rss/', 'tags' => ['analyst', 'mining']],
            ['type' => Source::TYPE_PUBLICATION, 'name' => 'Resource Investor', 'base_url' => 'https://resourceinvestor.com', 'feed_url' => 'https://resourceinvestor.com/feed/', 'tags' => ['analyst', 'mining']],

            // Newsletters / substacks
            ['type' => Source::TYPE_SUBSTACK, 'name' => 'Goehring & Rozencwajg', 'base_url' => 'https://gorozen.com', 'feed_url' => 'https://gorozen.com/feed', 'tags' => ['commodities', 'macro']],
            ['type' => Source::TYPE_SUBSTACK, 'name' => 'Crescat Capital Newsletter', 'base_url' => 'https://www.crescat.net', 'feed_url' => 'https://www.crescat.net/feed/', 'tags' => ['macro', 'precious-metals']],

            // Podcasts (initial ingestion = RSS metadata only; transcription is post-Phase-9)
            ['type' => Source::TYPE_PODCAST, 'name' => 'Crux Investor', 'base_url' => 'https://www.cruxinvestor.com', 'feed_url' => 'https://feeds.simplecast.com/9zKkA1lH', 'tags' => ['mining', 'ceo-interviews']],
            ['type' => Source::TYPE_PODCAST, 'name' => 'Mining Stock Education', 'base_url' => 'https://www.miningstockeducation.com', 'feed_url' => 'https://www.miningstockeducation.com/feed/podcast/', 'tags' => ['mining', 'ceo-interviews']],
            ['type' => Source::TYPE_PODCAST, 'name' => 'The Mining Investor', 'base_url' => 'https://www.themininginvestor.com', 'feed_url' => 'https://feeds.buzzsprout.com/2230807.rss', 'tags' => ['mining']],
            ['type' => Source::TYPE_PODCAST, 'name' => 'Palisades Gold Radio', 'base_url' => 'https://palisadesradio.ca', 'feed_url' => 'https://palisadesradio.ca/feed/podcast', 'tags' => ['gold', 'precious-metals']],
            ['type' => Source::TYPE_PODCAST, 'name' => 'Quoth The Raven', 'base_url' => 'https://quoththeraven.substack.com', 'feed_url' => 'https://feeds.simplecast.com/m0sUDx_z', 'tags' => ['contrarian', 'macro']],
        ];

        foreach ($sources as $row) {
            Source::updateOrCreate(
                ['scope' => Source::SCOPE_GLOBAL, 'name' => $row['name']],
                array_merge($row, [
                    'scope' => Source::SCOPE_GLOBAL,
                    'team_id' => null,
                    'ingest_strategy' => 'rss',
                    'is_active' => true,
                ])
            );
        }
    }
}
