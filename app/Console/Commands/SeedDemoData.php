<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Company;
use App\Models\ExtractedClaim;
use App\Models\MatchEvent;
use App\Models\MatchRecord;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seed a complete demo scenario so every page shows real-looking content.
 *
 * Creates (or reuses) a user + activated team, then populates:
 *   - 1 fictional JMC (Aurelian Gold Resources Corp)
 *   - 4 press releases spanning ~60 days with realistic analyzed data
 *   - ~6 global mining sources + 12 publication items + 5 authors
 *   - Extracted claims (including verified outcomes) for several items
 *   - 6 matches across statuses: new, contacted, placed, dismissed
 *   - Match events showing the workflow history
 *
 * Idempotent: deletes prior Aurelian data on the target team before reseeding.
 *
 *   php artisan app:seed-demo              # creates demo@prcomet.test / demo-password
 *   php artisan app:seed-demo --email=me@example.com   # uses existing user's team
 */
class SeedDemoData extends Command
{
    protected $signature = 'app:seed-demo
        {--email= : Existing user email whose team should receive the demo data}
        {--password=demo-password : Password to set if creating the default demo user}';

    protected $description = 'Populate the workspace with a fictional JMC and a full set of matches across all statuses';

    public function handle(): int
    {
        DB::transaction(function () {
            $user = $this->resolveUser();
            $team = $this->resolveTeam($user);

            $this->info("Seeding demo data into team {$team->name} (id {$team->id})…");

            $this->resetDemoData($team);
            $this->ensureSourceCorpus();

            $company = $this->seedCompany($team);
            $releases = $this->seedPressReleases($company);
            $authors = $this->seedAuthors();
            $items = $this->seedPublicationItems($authors);
            $this->seedClaims($items, $authors);
            $this->seedMatches($company, $releases, $items, $authors, $user);

            $this->printSummary($user, $team, $company);
        });

        return self::SUCCESS;
    }

    private function resolveUser(): User
    {
        $email = (string) ($this->option('email') ?? '');

        if ($email !== '') {
            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->error("No user found with email {$email}.");
                throw new \RuntimeException('User not found');
            }
            return $user;
        }

        $email = 'demo@prcomet.test';
        $user = User::where('email', $email)->first();
        if ($user) {
            return $user;
        }

        $password = (string) $this->option('password');
        $user = new User;
        $user->forceFill([
            'name' => 'Demo User',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ])->save();

        $this->info("Created demo user {$email} / {$password}");
        return $user;
    }

    private function resolveTeam(User $user): Team
    {
        $team = $user->currentTeam ?? $user->ownedTeams()->first();

        if (! $team) {
            // Brand-new user from the demo path — Jetstream usually creates a
            // personal team on signup but we bypassed that, so do it here.
            $team = $user->ownedTeams()->create([
                'name' => "{$user->name}'s Team",
                'personal_team' => true,
            ]);
            $user->forceFill(['current_team_id' => $team->id])->save();
        }

        if (! $team->is_active) {
            $team->forceFill([
                'is_active' => true,
                'max_companies' => 5,
                'activated_at' => now(),
            ])->save();
        }

        return $team;
    }

    private function resetDemoData(Team $team): void
    {
        // Cascade deletes via FKs clean up press releases, matches, events.
        Company::where('team_id', $team->id)
            ->where('name', 'Aurelian Gold Resources Corp')
            ->delete();
    }

    private function ensureSourceCorpus(): void
    {
        if (Source::where('scope', Source::SCOPE_GLOBAL)->doesntExist()) {
            $this->info('Seeding global mining source corpus…');
            $this->call('db:seed', ['--class' => 'MiningSourceSeeder']);
        }
    }

    private function seedCompany(Team $team): Company
    {
        return Company::create([
            'team_id' => $team->id,
            'name' => 'Aurelian Gold Resources Corp',
            'ticker' => 'AUR',
            'exchange' => 'TSX-V',
            'website' => 'https://aureliangold.example',
            'rss_feed_url' => 'https://aureliangold.example/news/rss',
            'ir_contact_name' => 'Sarah Chen',
            'ir_contact_email' => 'schen@aureliangold.example',
            'ir_contact_phone' => '+1 604 555 0123',
            'sector_tags' => ['gold', 'silver', 'nevada', 'walker-lane'],
            'is_active' => true,
            'last_ingested_at' => now()->subHours(2),
        ]);
    }

    /** @return array<string, PressRelease> */
    private function seedPressReleases(Company $company): array
    {
        $releases = [];

        $releases['drill'] = PressRelease::create([
            'company_id' => $company->id,
            'external_guid' => 'aur-2026-05-17-001',
            'source_url' => 'https://aureliangold.example/news/big-sky-drill-results-may-2026',
            'title' => 'Aurelian intersects 12.4 g/t Au over 28m at Big Sky',
            'body_text' => "VANCOUVER, BC — Aurelian Gold Resources Corp (TSX-V: AUR) is pleased to announce results from hole AUR-26-001 at its 100%-owned Big Sky project in Nevada's Walker Lane trend. The hole returned 12.4 g/t Au over 28m, including 24.6 g/t Au over 8m, within the previously announced Hanging Wall Zone.\n\n\"This intercept confirms the continuity we hoped to see along the structural trend,\" said CEO Sarah Chen. \"We are accelerating Phase II drilling to test the strike extension to the north.\"\n\nThe Big Sky property covers 8,400 hectares immediately south of the Round Mountain trend.",
            'body_html' => '<p>VANCOUVER, BC — Aurelian Gold Resources Corp...</p>',
            'published_at' => now()->subDays(10),
            'analysis_status' => PressRelease::ANALYSIS_DONE,
            'analyzed_at' => now()->subDays(10)->addMinutes(8),
            'extracted_entities' => [
                'commodities' => ['gold'],
                'jurisdictions' => ['Nevada', 'Walker Lane'],
                'projects' => ['Big Sky'],
                'people' => ['Sarah Chen'],
            ],
            'extracted_topics' => ['drill-results', 'gold', 'nevada', 'walker-lane', 'high-grade'],
            'stance' => 'bullish',
            'key_claims' => [
                ['claim' => '12.4 g/t Au over 28m intercept at Big Sky from hole AUR-26-001', 'type' => 'result'],
                ['claim' => 'Includes 24.6 g/t Au over 8m within Hanging Wall Zone', 'type' => 'result'],
                ['claim' => 'Phase II drilling accelerated to test north strike extension', 'type' => 'guidance'],
            ],
        ]);

        $releases['placement'] = PressRelease::create([
            'company_id' => $company->id,
            'external_guid' => 'aur-2026-05-02-001',
            'source_url' => 'https://aureliangold.example/news/c8m-private-placement-closes',
            'title' => 'Aurelian Gold closes C$8M non-brokered private placement',
            'body_text' => "VANCOUVER, BC — Aurelian Gold Resources Corp (TSX-V: AUR) has closed its previously announced non-brokered private placement, raising aggregate gross proceeds of C\$8,000,000 through the issuance of 17,777,777 units at C\$0.45 per unit. Each unit consists of one common share and one-half of one common share purchase warrant exercisable at C\$0.65 for 24 months. Proceeds will fund the Phase II drill program at Big Sky and general working capital.",
            'body_html' => '<p>VANCOUVER, BC — Aurelian Gold...</p>',
            'published_at' => now()->subDays(25),
            'analysis_status' => PressRelease::ANALYSIS_DONE,
            'analyzed_at' => now()->subDays(25)->addMinutes(6),
            'extracted_entities' => [
                'commodities' => ['gold'],
                'jurisdictions' => ['Nevada'],
                'projects' => ['Big Sky'],
                'people' => [],
            ],
            'extracted_topics' => ['financing', 'private-placement', 'gold'],
            'stance' => 'informational',
            'key_claims' => [
                ['claim' => 'Closed C$8M non-brokered placement at C$0.45/unit', 'type' => 'financing'],
                ['claim' => 'Proceeds fund Phase II drilling and working capital', 'type' => 'corporate'],
            ],
        ]);

        $releases['phase2'] = PressRelease::create([
            'company_id' => $company->id,
            'external_guid' => 'aur-2026-04-12-001',
            'source_url' => 'https://aureliangold.example/news/phase-ii-drill-program',
            'title' => 'Phase II drill program commences at Big Sky',
            'body_text' => 'VANCOUVER, BC — Aurelian Gold Resources Corp (TSX-V: AUR) has commenced the Phase II drill program at its Big Sky gold project, Nevada. The 10,000-metre program will test extensions to the Hanging Wall and Footwall zones identified in the Phase I program.',
            'body_html' => '<p>...</p>',
            'published_at' => now()->subDays(45),
            'analysis_status' => PressRelease::ANALYSIS_DONE,
            'analyzed_at' => now()->subDays(45)->addMinutes(5),
            'extracted_entities' => [
                'commodities' => ['gold'],
                'jurisdictions' => ['Nevada'],
                'projects' => ['Big Sky'],
                'people' => [],
            ],
            'extracted_topics' => ['drilling', 'exploration', 'nevada', 'gold'],
            'stance' => 'informational',
            'key_claims' => [
                ['claim' => '10,000m Phase II drill program underway', 'type' => 'guidance'],
            ],
        ]);

        $releases['q1'] = PressRelease::create([
            'company_id' => $company->id,
            'external_guid' => 'aur-2026-03-28-001',
            'source_url' => 'https://aureliangold.example/news/q1-2026-corporate-update',
            'title' => 'Aurelian Gold Q1 2026 corporate update — land position expanded to 8,400 ha',
            'body_text' => 'VANCOUVER, BC — Aurelian Gold Resources Corp (TSX-V: AUR) reports Q1 2026 corporate updates. Strategic staking has increased the Big Sky land package to 8,400 hectares, providing exposure to additional Walker Lane structural targets.',
            'body_html' => '<p>...</p>',
            'published_at' => now()->subDays(60),
            'analysis_status' => PressRelease::ANALYSIS_DONE,
            'analyzed_at' => now()->subDays(60)->addMinutes(5),
            'extracted_entities' => [
                'commodities' => ['gold'],
                'jurisdictions' => ['Nevada', 'Walker Lane'],
                'projects' => ['Big Sky'],
                'people' => [],
            ],
            'extracted_topics' => ['corporate', 'land-position', 'nevada'],
            'stance' => 'informational',
            'key_claims' => [
                ['claim' => 'Big Sky land package expanded to 8,400 ha', 'type' => 'corporate'],
            ],
        ]);

        return $releases;
    }

    /** @return array<string, Author> */
    private function seedAuthors(): array
    {
        return [
            'sinclair' => Author::firstOrCreate(['slug' => 'robert-sinclair-demo'], [
                'name' => 'Robert Sinclair',
                'bio' => 'Mining.com staff writer covering Nevada and western US precious metals.',
                'x_handle' => '@rsinclair_mining',
                'email' => 'rsinclair@mining.example',
                'body_of_work_summary' => 'Sinclair specializes in Nevada and western US gold/silver coverage, with strong focus on the Walker Lane and Carlin trends. Generally constructive on high-grade discoveries; tends to follow up on companies he\'s flagged earlier in a quarter. Recent pieces (last 60 days) include "Walker Lane: another gold rush?" and "Nevada drill season heats up: five names to watch."',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
            'lutz' => Author::firstOrCreate(['slug' => 'kerry-lutz-demo'], [
                'name' => 'Kerry Lutz',
                'bio' => 'Host of Mining Stock Education podcast.',
                'x_handle' => '@kerrylutz',
                'body_of_work_summary' => 'Long-running mining podcast host; books CEOs of small-cap explorers, with a strong bias toward sub-$50M market cap stories with defined catalyst timelines. Last quarter\'s guests skewed heavily toward gold in stable North American jurisdictions. Recently aired a strong Nevada-gold thesis episode that maps cleanly to drill-result follow-ups.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
            'taggart' => Author::firstOrCreate(['slug' => 'adam-taggart-demo'], [
                'name' => 'Adam Taggart',
                'bio' => 'Founder of Crux Investor; macro-focused podcast host.',
                'x_handle' => '@adam_taggart',
                'body_of_work_summary' => 'Macro-focused host who weaves commodity thesis into company interviews. Bullish on gold, lithium, and uranium throughout 2026. Frequently challenges guest CEOs on grade vs. tonnage tradeoffs and on jurisdictional risk. Published a notable H2 2026 gold macro piece in the last fortnight.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
            'cook' => Author::firstOrCreate(['slug' => 'brent-cook-demo'], [
                'name' => 'Brent Cook',
                'bio' => 'Geologist and exploration-focused newsletter writer.',
                'x_handle' => '@brent_cook',
                'body_of_work_summary' => 'Veteran geologist whose newsletter scrutinizes drill results and assay technique closely. Highlighted Pan American Silver\'s 2022 discovery weeks before broader coverage. Cares about geology, not promotion — pitches without a clear structural story tend to be ignored. Recent column on interpreting narrow-vein vs. wide-disseminated intercepts is a near-direct analog for high-grade Walker Lane plays.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
            'day' => Author::firstOrCreate(['slug' => 'adrian-day-demo'], [
                'name' => 'Adrian Day',
                'bio' => 'Resource fund manager and newsletter writer.',
                'x_handle' => '@adrian_day',
                'body_of_work_summary' => 'Veteran resource investor focused on royalty companies and developers near production. Bullish on gold long-term but conspicuously skeptical of early-stage explorers without a clear path to economic study. Recent piece "Why I\'m cautious on most JMCs" is a direct contrarian framing that makes him an awkward pitch target for pure-exploration stories.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
        ];
    }

    /** @return array<string, PublicationItem> */
    private function seedPublicationItems(array $authors): array
    {
        // Find or create the sources we want.
        $sources = $this->resolveSources();

        $items = [];

        $items['sinclair_drill_watch'] = $this->makeItem($sources['mining_com'], $authors['sinclair'], [
            'title' => 'Nevada drill season heats up: five names to watch this quarter',
            'url' => 'https://mining.example/nevada-drill-season-watch-2026',
            'published_at' => now()->subDays(4),
            'body_text' => 'With drill rigs across the Walker Lane and Carlin trends spinning, this quarter looks like one of the strongest in a decade for Nevada gold juniors. Five names worth tracking include Aurelian-style high-grade plays in the southern Walker Lane, where structural settings are setting up well…',
            'extracted_topics' => ['nevada', 'gold', 'walker-lane', 'drill-results', 'juniors'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => ['Nevada', 'Walker Lane'], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['sinclair_walker_lane'] = $this->makeItem($sources['mining_com'], $authors['sinclair'], [
            'title' => 'Walker Lane: another gold rush?',
            'url' => 'https://mining.example/walker-lane-gold-rush',
            'published_at' => now()->subDays(35),
            'body_text' => 'The Walker Lane structural belt is having a moment. After two decades as the Carlin Trend\'s less-glamorous cousin, a string of high-grade discoveries is putting it on the map…',
            'extracted_topics' => ['walker-lane', 'gold', 'nevada', 'analysis'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => ['Nevada', 'Walker Lane'], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['lutz_nevada_bull'] = $this->makeItem($sources['mse_podcast'], $authors['lutz'], [
            'title' => 'Why Nevada gold is the most underpriced asset (with CEO Mark Reynolds)',
            'url' => 'https://mse.example/episode-nevada-gold-reynolds',
            'published_at' => now()->subDays(8),
            'body_text' => 'In this episode, host Kerry Lutz speaks with CEO Mark Reynolds about why Nevada-focused gold explorers are trading at depression-era valuations relative to gold spot. Walker Lane and Carlin both feature prominently…',
            'extracted_topics' => ['nevada', 'gold', 'valuations', 'ceo-interview'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => ['Nevada'], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['lutz_reading_drills'] = $this->makeItem($sources['mse_podcast'], $authors['lutz'], [
            'title' => 'Reading drill results: what to look for',
            'url' => 'https://mse.example/episode-reading-drill-results',
            'published_at' => now()->subDays(40),
            'body_text' => 'Lutz walks listeners through how to evaluate JMC drill results, with examples from recent quarters…',
            'extracted_topics' => ['drill-results', 'education', 'gold'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'informational',
        ]);

        $items['taggart_macro_gold'] = $this->makeItem($sources['crux'], $authors['taggart'], [
            'title' => 'Macro setup for gold in H2 2026',
            'url' => 'https://crux.example/macro-gold-h2-2026',
            'published_at' => now()->subDays(12),
            'body_text' => 'The macro setup for H2 2026 looks constructive for gold. Real rates are rolling over, central bank buying continues, and supply growth from majors is anemic. The leverage for explorers with cash on hand is meaningful…',
            'extracted_topics' => ['gold', 'macro', 'h2-2026', 'thesis'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['cook_drill_decoded'] = $this->makeItem($sources['cook_newsletter'], $authors['cook'], [
            'title' => 'Drill results decoded: what 12 g/t over 28m really means',
            'url' => 'https://cook.example/drill-results-decoded',
            'published_at' => now()->subDays(15),
            'body_text' => 'A wider intercept at moderate-to-high grade tells you something fundamentally different than a high-grade narrow vein. Continuity. Mining width. Both matter more than the headline g/t. Here\'s how to think about it…',
            'extracted_topics' => ['drill-results', 'geology', 'education', 'gold'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'informational',
        ]);

        $items['day_cautious'] = $this->makeItem($sources['day_newsletter'], $authors['day'], [
            'title' => "Why I'm cautious on most JMCs this cycle",
            'url' => 'https://day.example/cautious-on-jmcs',
            'published_at' => now()->subDays(3),
            'body_text' => 'Despite the constructive macro, most junior miners will not deliver returns this cycle. Capital is scarce, dilution is brutal, and the path to economic study is longer than CEOs admit…',
            'extracted_topics' => ['jmc', 'cautious', 'contrarian'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'contrarian',
        ]);

        $items['day_royalty'] = $this->makeItem($sources['day_newsletter'], $authors['day'], [
            'title' => 'Royalty companies for the gold cycle',
            'url' => 'https://day.example/royalty-companies-gold-cycle',
            'published_at' => now()->subDays(50),
            'body_text' => 'If you want gold exposure without exploration risk, the royalty model is the cleanest expression…',
            'extracted_topics' => ['royalty', 'gold', 'thesis'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'bullish',
        ]);

        // A few unrelated items so the candidate filter has noise to ignore.
        $items['lithium_glut'] = $this->makeItem($sources['mining_com'], $authors['sinclair'], [
            'title' => 'Lithium glut: supply story still overstated',
            'url' => 'https://mining.example/lithium-glut-overstated',
            'published_at' => now()->subDays(20),
            'body_text' => 'Lithium prices have stabilized, but the supply glut narrative remains overstated…',
            'extracted_topics' => ['lithium', 'supply', 'contrarian'],
            'entities' => ['commodities' => ['lithium'], 'jurisdictions' => ['Australia'], 'companies' => []],
            'stance' => 'contrarian',
        ]);

        $items['copper_thesis'] = $this->makeItem($sources['crux'], $authors['taggart'], [
            'title' => 'Copper bull thesis revisited',
            'url' => 'https://crux.example/copper-bull-revisited',
            'published_at' => now()->subDays(28),
            'body_text' => 'The copper bull case continues to look strong heading into electrification capex cycles…',
            'extracted_topics' => ['copper', 'macro'],
            'entities' => ['commodities' => ['copper'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['cook_off_topic'] = $this->makeItem($sources['cook_newsletter'], $authors['cook'], [
            'title' => 'A South American silver story worth a second look',
            'url' => 'https://cook.example/south-american-silver',
            'published_at' => now()->subDays(22),
            'body_text' => 'Sometimes the most overlooked stories are hiding in plain sight…',
            'extracted_topics' => ['silver', 'south-america', 'analysis'],
            'entities' => ['commodities' => ['silver'], 'jurisdictions' => ['Peru'], 'companies' => []],
            'stance' => 'bullish',
        ]);

        return $items;
    }

    /** @return array<string, Source> */
    private function resolveSources(): array
    {
        return [
            'mining_com' => Source::firstOrCreate(
                ['scope' => Source::SCOPE_GLOBAL, 'name' => 'Mining.com'],
                ['type' => Source::TYPE_PUBLICATION, 'feed_url' => 'https://www.mining.com/feed/', 'ingest_strategy' => 'rss', 'is_active' => true, 'tags' => ['general', 'mining']]
            ),
            'mse_podcast' => Source::firstOrCreate(
                ['scope' => Source::SCOPE_GLOBAL, 'name' => 'Mining Stock Education'],
                ['type' => Source::TYPE_PODCAST, 'feed_url' => 'https://www.miningstockeducation.com/feed/podcast/', 'ingest_strategy' => 'rss', 'is_active' => true, 'tags' => ['mining', 'ceo-interviews']]
            ),
            'crux' => Source::firstOrCreate(
                ['scope' => Source::SCOPE_GLOBAL, 'name' => 'Crux Investor'],
                ['type' => Source::TYPE_PODCAST, 'feed_url' => 'https://feeds.simplecast.com/9zKkA1lH', 'ingest_strategy' => 'rss', 'is_active' => true, 'tags' => ['mining', 'macro']]
            ),
            'cook_newsletter' => Source::firstOrCreate(
                ['scope' => Source::SCOPE_GLOBAL, 'name' => 'Exploration Insights (Brent Cook)'],
                ['type' => Source::TYPE_SUBSTACK, 'feed_url' => 'https://explorationinsights.example/feed', 'ingest_strategy' => 'rss', 'is_active' => true, 'tags' => ['geology', 'exploration']]
            ),
            'day_newsletter' => Source::firstOrCreate(
                ['scope' => Source::SCOPE_GLOBAL, 'name' => 'Adrian Day Global Analyst'],
                ['type' => Source::TYPE_SUBSTACK, 'feed_url' => 'https://adriandayglobal.example/feed', 'ingest_strategy' => 'rss', 'is_active' => true, 'tags' => ['analyst', 'royalty']]
            ),
        ];
    }

    private function makeItem(Source $source, Author $author, array $attrs): PublicationItem
    {
        return PublicationItem::create([
            'source_id' => $source->id,
            'author_id' => $author->id,
            'external_guid' => 'demo-'.Str::random(16),
            'url' => $attrs['url'],
            'title' => $attrs['title'],
            'body_text' => $attrs['body_text'],
            'published_at' => $attrs['published_at'],
            'analysis_status' => PublicationItem::ANALYSIS_DONE,
            'analyzed_at' => $attrs['published_at']->copy()->addMinutes(15),
            'extracted_topics' => $attrs['extracted_topics'],
            'extracted_entities' => $attrs['entities'],
            'stance' => $attrs['stance'],
        ]);
    }

    private function seedClaims(array $items, array $authors): void
    {
        // A correct prediction (silver $25 by Q1 2026 — already verified)
        ExtractedClaim::create([
            'publication_item_id' => $items['day_cautious']->id,
            'author_id' => $authors['day']->id,
            'claim_text' => 'Most JMCs will underperform gold this cycle.',
            'topic' => 'gold',
            'stance' => 'contrarian',
            'predicted_outcome' => null,
            'timeframe' => null,
            'verified_outcome' => ExtractedClaim::VERIFIED_UNVERIFIABLE,
            'verified_at' => now()->subDays(2),
            'verification_evidence' => ['reason' => 'qualitative claim — no testable threshold'],
        ]);

        ExtractedClaim::create([
            'publication_item_id' => $items['taggart_macro_gold']->id,
            'author_id' => $authors['taggart']->id,
            'claim_text' => 'Real rates rolling over set up a constructive H2 2026 for gold.',
            'topic' => 'gold',
            'stance' => 'bullish',
            'predicted_outcome' => 'above $2400',
            'timeframe' => 'Q4 2026',
        ]);

        // A claim that was previously verified correct — shows a track record.
        ExtractedClaim::create([
            'publication_item_id' => $items['sinclair_walker_lane']->id,
            'author_id' => $authors['sinclair']->id,
            'claim_text' => 'Walker Lane discoveries will accelerate through 2026.',
            'topic' => 'gold',
            'stance' => 'bullish',
            'predicted_outcome' => 'multiple new discoveries',
            'timeframe' => 'Q1 2026',
            'verified_outcome' => ExtractedClaim::VERIFIED_CORRECT,
            'verified_at' => now()->subDays(30),
            'verification_evidence' => [
                'evidence' => '3 new Walker Lane discoveries publicly announced in Q1 2026.',
            ],
        ]);
    }

    private function seedMatches(Company $company, array $releases, array $items, array $authors, User $user): void
    {
        $drillRelease = $releases['drill'];
        $placementRelease = $releases['placement'];

        // 1) NEW · Robert Sinclair · drill-watch piece — strongest match.
        MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['sinclair_drill_watch']->id,
            'author_id' => $authors['sinclair']->id,
            'score' => 0.87,
            'rationale_md' => "Sinclair published \"Nevada drill season heats up: five names to watch\" 4 days ago, calling out Walker Lane high-grade plays as the most interesting setups of the quarter. Aurelian's fresh 12.4 g/t Au over 28m intercept at Big Sky (Walker Lane, Nevada) lands squarely in that frame. Sinclair has a documented pattern of follow-up coverage on companies he’s previously flagged when they hit strong drill results — see his March follow-up on Eclipse Mining after his earlier name-check.",
            'suggested_angle_md' => 'Email Sinclair directly with the AUR-26-001 intercept summary (already on the wire) plus a brief on what Phase II will test. Offer Sarah Chen for a 15-minute call. Keep it factual — Sinclair dislikes promotional framing.',
            'citations' => [
                ['publication_item_id' => $items['sinclair_drill_watch']->id, 'quote' => 'Nevada drill season heats up: five names to watch this quarter'],
                ['publication_item_id' => $items['sinclair_walker_lane']->id, 'quote' => 'Walker Lane: another gold rush?'],
            ],
            'status' => MatchRecord::STATUS_NEW,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        // 2) NEW · Kerry Lutz · Nevada-bull podcast episode.
        MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['lutz_nevada_bull']->id,
            'author_id' => $authors['lutz']->id,
            'score' => 0.81,
            'rationale_md' => "Lutz aired \"Why Nevada gold is the most underpriced asset\" 8 days ago — the entire episode argues precisely the thesis Aurelian's intercept now reinforces. Lutz routinely books CEOs of names that confirm the thesis his recent episodes articulated; his last quarter shows a 7-out-of-9 conversion from thesis episodes to follow-up CEO interviews on the same trend.",
            'suggested_angle_md' => 'Pitch a CEO interview slotted as a follow-up to the Reynolds episode. Frame it: "You said Nevada gold is underpriced. Here\'s a drill result that says it might also be undermarketed." Lutz responds to that kind of episodic continuity.',
            'citations' => [
                ['publication_item_id' => $items['lutz_nevada_bull']->id, 'quote' => 'Why Nevada gold is the most underpriced asset (with CEO Mark Reynolds)'],
            ],
            'status' => MatchRecord::STATUS_NEW,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        // 3) NEW · Brent Cook · drill-results-decoded piece.
        MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['cook_drill_decoded']->id,
            'author_id' => $authors['cook']->id,
            'score' => 0.76,
            'rationale_md' => "Cook’s \"Drill results decoded\" column 15 days ago dissected exactly the type of intercept Aurelian just reported — wider intercept at moderate-to-high grade, where continuity and mining width matter more than headline g/t. He explicitly praised this geometry over narrow-vein high-grade. AUR-26-001 (28m at 12.4 g/t) is a near-perfect fit for his preferred pattern.",
            'suggested_angle_md' => 'Send Cook the long-section showing continuity along strike. Skip the executive bio and macro framing — he wants geology. Include drill collar coordinates, dip, and your interpretation of the structural control.',
            'citations' => [
                ['publication_item_id' => $items['cook_drill_decoded']->id, 'quote' => 'Drill results decoded: what 12 g/t over 28m really means'],
            ],
            'status' => MatchRecord::STATUS_NEW,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        // 4) CONTACTED · Adam Taggart · macro piece, against placement PR.
        $contactedMatch = MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $placementRelease->id,
            'publication_item_id' => $items['taggart_macro_gold']->id,
            'author_id' => $authors['taggart']->id,
            'score' => 0.79,
            'rationale_md' => "Taggart’s \"Macro setup for gold in H2 2026\" 12 days ago argues real rates rolling over and central bank buying make H2 a constructive tape for explorers with cash. Aurelian just closed C\$8M, giving them treasury through Phase II drilling into exactly the window Taggart called out. He routinely books CEOs whose corporate moves align with his macro framing.",
            'suggested_angle_md' => 'Pitch Sarah for a 20-minute Crux Investor interview. Lead with the macro tie-in: "You wrote the H2 setup last week. We just funded a 10,000m program into that window." Taggart responds to companies that engage with his thesis, not those who just want airtime.',
            'citations' => [
                ['publication_item_id' => $items['taggart_macro_gold']->id, 'quote' => 'Macro setup for gold in H2 2026'],
            ],
            'status' => MatchRecord::STATUS_CONTACTED,
            'created_at' => now()->subDays(24),
            'updated_at' => now()->subDays(6),
        ]);

        MatchEvent::create([
            'match_id' => $contactedMatch->id,
            'user_id' => $user->id,
            'event_type' => MatchEvent::TYPE_SAVED,
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);
        MatchEvent::create([
            'match_id' => $contactedMatch->id,
            'user_id' => $user->id,
            'event_type' => MatchRecord::STATUS_CONTACTED,
            'notes_md' => 'Sent Adam an email referencing his macro piece + our placement close. Offered a 20-min slot for Sarah. Awaiting reply.',
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        // 5) PLACED · Robert Sinclair · Walker Lane piece, against drill PR.
        $placedMatch = MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['sinclair_walker_lane']->id,
            'author_id' => $authors['sinclair']->id,
            'score' => 0.83,
            'rationale_md' => "Sinclair’s \"Walker Lane: another gold rush?\" piece 35 days ago framed the belt as a structural revival story. Aurelian’s intercept extends precisely that narrative — high-grade, Walker Lane, on-strike with established structures Sinclair named. A direct contribution to his ongoing trend coverage rather than a one-off pitch.",
            'suggested_angle_md' => 'Send Sinclair the intercept + the structural map showing on-strike continuity with the trend names from his Walker Lane piece. Position it as "the next data point in your trend coverage" not as a standalone announcement.',
            'citations' => [
                ['publication_item_id' => $items['sinclair_walker_lane']->id, 'quote' => 'Walker Lane: another gold rush?'],
            ],
            'status' => MatchRecord::STATUS_PLACED,
            'placement_url' => 'https://mining.example/aurelian-big-sky-walker-lane-2026',
            'placement_title' => "Aurelian's Big Sky intercept adds to Walker Lane revival story",
            'placement_description' => 'TSX-V junior Aurelian Gold delivered a 12.4 g/t Au over 28m intercept at its Nevada Big Sky project, the latest in a string of strong drill returns from the Walker Lane belt — and the kind of structural follow-through that suggests the trend is for real.',
            'placement_published_at' => now()->subDays(4),
            'placement_fetched_at' => now()->subDays(4),
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(4),
        ]);

        MatchEvent::create([
            'match_id' => $placedMatch->id,
            'user_id' => $user->id,
            'event_type' => MatchEvent::TYPE_SAVED,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);
        MatchEvent::create([
            'match_id' => $placedMatch->id,
            'user_id' => $user->id,
            'event_type' => MatchRecord::STATUS_CONTACTED,
            'notes_md' => 'Sent the intercept summary + on-strike continuity map to Sinclair. Referenced his Walker Lane piece directly.',
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(7),
        ]);
        MatchEvent::create([
            'match_id' => $placedMatch->id,
            'user_id' => $user->id,
            'event_type' => MatchRecord::STATUS_PLACED,
            'notes_md' => 'Article published — Sinclair tied us directly into his ongoing Walker Lane trend coverage.',
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        // 6) DISMISSED · Adrian Day · cautious-on-JMCs piece.
        $dismissedMatch = MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['day_cautious']->id,
            'author_id' => $authors['day']->id,
            'score' => 0.62,
            'rationale_md' => "Day covers gold but his \"Why I’m cautious on most JMCs\" piece 3 days ago is explicitly contrarian on exactly the type of story Aurelian represents. Topic overlap is real (gold, JMC) but his current public position makes a productive pitch unlikely without a near-term economic study to anchor the conversation.",
            'suggested_angle_md' => 'Better to revisit Day when Aurelian has a PEA in hand. A pitch now would force him to either contradict his recent piece or politely decline.',
            'citations' => [
                ['publication_item_id' => $items['day_cautious']->id, 'quote' => "Why I'm cautious on most JMCs this cycle"],
            ],
            'status' => MatchRecord::STATUS_DISMISSED,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(8),
        ]);

        MatchEvent::create([
            'match_id' => $dismissedMatch->id,
            'user_id' => $user->id,
            'event_type' => MatchRecord::STATUS_DISMISSED,
            'notes_md' => "Adrian's publicly cautious on exploration-stage JMCs right now. Better to wait until we have a PEA before approaching him.",
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);
    }

    private function printSummary(User $user, Team $team, Company $company): void
    {
        $this->newLine();
        $this->info('✅ Demo data seeded.');
        $this->newLine();
        $this->line("Team:      {$team->name}");
        $this->line("Owner:     {$user->name} <{$user->email}>");
        $this->line("Company:   {$company->name} ({$company->ticker}.{$company->exchange})");
        $this->newLine();

        $this->line('What you can see in the workspace:');
        $this->line('  • <fg=yellow>/dashboard</> — counts populated, recent matches feed');
        $this->line('  • <fg=yellow>/dashboard/companies/'.$company->id.'</> — 4 press releases');
        $this->line('  • <fg=yellow>/dashboard/matches</> — 3 new · 1 contacted · 1 placed · 1 dismissed');
        $this->line('  • <fg=yellow>/dashboard/wins</> — the placed match with full attribution');
        $this->newLine();

        if ($user->email === 'demo@prcomet.test') {
            $this->line('Login: <fg=cyan>demo@prcomet.test</> / <fg=cyan>'.$this->option('password').'</>');
            $this->newLine();
        }
    }
}
