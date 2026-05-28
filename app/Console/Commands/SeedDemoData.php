<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Company;
use App\Models\ExtractedClaim;
use App\Models\MatchEvent;
use App\Models\MatchRecord;
use App\Models\MediaAsset;
use App\Models\OnePager;
use App\Models\OnePagerView;
use App\Models\PressRelease;
use App\Models\PublicationItem;
use App\Models\Source;
use App\Models\Team;
use App\Models\User;
use App\Models\Watch;
use App\Models\WatchHit;
use App\Services\Observatory\WatchScanner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seed a complete demo scenario so every page shows real-looking content.
 *
 * Creates (or reuses) a user + activated team, then populates:
 *   - 1 fictional JMC (Aurelian Gold Resources Corp) with full branding
 *   - 4 press releases spanning ~60 days with realistic analyzed data
 *   - ~6 global mining sources + 12 publication items + 5 authors
 *   - Extracted claims (including verified outcomes)
 *   - 6 matches across statuses: new, contacted, placed, dismissed
 *   - Match events showing workflow history
 *   - Media Library: logo, header, 3 project images, 2 pull quotes, 1 link
 *   - 1 published OnePager with curated assets, a note, and recent view data
 *
 * Idempotent: deletes prior Aurelian data on the target team before reseeding.
 */
class SeedDemoData extends Command
{
    protected $signature = 'app:seed-demo
        {--email= : Existing user email whose team should receive the demo data}
        {--password=demo-password : Password to set if creating the default demo user}
        {--observatory-only : Only seed Observatory watches against the existing Aurelian company; leaves everything else alone}';

    protected $description = 'Populate the workspace with a fictional JMC and a full set of matches across all statuses';

    public function handle(): int
    {
        DB::transaction(function () {
            $user = $this->resolveUser();
            $team = $this->resolveTeam($user);

            if ($this->option('observatory-only')) {
                $this->info("Seeding Observatory watches into team {$team->name} (id {$team->id})…");
                $company = Company::where('team_id', $team->id)
                    ->where('name', 'Aurelian Gold Resources Corp')
                    ->first();

                if (! $company) {
                    $this->error('No "Aurelian Gold Resources Corp" company found on this team — run the full seeder first.');
                    return;
                }

                // Wipe any prior watches on this company so re-runs are clean.
                Watch::where('company_id', $company->id)->delete();
                $this->seedObservatory($team, $company, $user);
                $this->info("Done. Visit /dashboard/companies/{$company->id}/observatory");
                return;
            }

            $this->info("Seeding demo data into team {$team->name} (id {$team->id})…");

            $this->resetDemoData($team);
            $this->ensureSourceCorpus();

            $company = $this->seedCompany($team);
            $this->seedBrandingFiles($company);
            $releases = $this->seedPressReleases($company);
            $authors = $this->seedAuthors();
            $items = $this->seedPublicationItems($authors);
            $this->seedClaims($items, $authors);
            $matches = $this->seedMatches($company, $releases, $items, $authors, $user);

            $this->seedMediaAssets($company, $releases);
            $onePager = $this->seedOnePager($matches['placed'], $company, $user);
            $this->seedObservatory($team, $company, $user);

            $this->printSummary($user, $team, $company, $onePager);
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
        Company::where('team_id', $team->id)
            ->where('name', 'Aurelian Gold Resources Corp')
            ->delete();

        // Publication items intentionally stay put. They're global (anchored
        // to team-visible sources) and shared across every demo seed, so
        // deleting them would cascade-delete the matches of every OTHER
        // demo user pointing at the same items. Idempotency on the items
        // themselves is handled in makeItem() via firstOrCreate-by-URL.
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
            // Branding
            'accent_color' => '#B8501D',
            'tagline' => 'Nevada-focused gold explorer chasing high-grade Walker Lane targets.',
            'description_md' => "Aurelian Gold Resources Corp (TSX-V: AUR) is a junior gold exploration company focused on the under-explored southern Walker Lane belt of Nevada. Our flagship Big Sky project covers 8,400 hectares immediately south of the Round Mountain trend and hosts multiple high-grade gold targets.\n\nThe company is led by an experienced team with prior discoveries in Nevada, BC, and Quebec. We're funded through Phase II drilling with a fully-financed C\$8M treasury.",
            'press_contact_email' => 'press@aureliangold.example',
            'social_links' => [
                'twitter' => 'https://x.com/aureliangold',
                'linkedin' => 'https://linkedin.com/company/aurelian-gold',
            ],
        ]);
    }

    /**
     * Generate placeholder SVG files for the company's logo and header, plus
     * a couple of project photos, and write them to the public disk so the
     * one-pager renders with real images.
     */
    private function seedBrandingFiles(Company $company): void
    {
        $disk = Storage::disk('public');

        // Logo: AUR monogram + wordmark on a transparent background.
        $logo = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 80">
    <defs>
        <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#B8501D"/>
            <stop offset="100%" stop-color="#7C2D12"/>
        </linearGradient>
    </defs>
    <rect x="0" y="10" width="60" height="60" rx="6" fill="url(#g)"/>
    <text x="30" y="56" text-anchor="middle" font-family="serif" font-size="36" font-weight="700" fill="white">A</text>
    <text x="78" y="38" font-family="sans-serif" font-size="22" font-weight="700" fill="#FFFFFF" letter-spacing="1">AURELIAN</text>
    <text x="78" y="62" font-family="sans-serif" font-size="14" font-weight="500" fill="#FCD9C2" letter-spacing="4">GOLD · TSX-V: AUR</text>
</svg>
SVG;
        $logoPath = "branding/{$company->id}/logo.svg";
        $disk->put($logoPath, $logo);

        // Header: warm gradient with a subtle topographic feel.
        $header = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1500 500" preserveAspectRatio="xMidYMid slice">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#1E293B"/>
            <stop offset="60%" stop-color="#7C2D12"/>
            <stop offset="100%" stop-color="#B8501D"/>
        </linearGradient>
        <filter id="noise">
            <feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="2"/>
            <feColorMatrix values="0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  0 0 0 0.04 0"/>
        </filter>
    </defs>
    <rect width="1500" height="500" fill="url(#bg)"/>
    <rect width="1500" height="500" filter="url(#noise)" opacity="0.4"/>
    <path d="M0 380 Q 250 320 500 350 T 1000 360 T 1500 340 L 1500 500 L 0 500 Z" fill="#0F172A" opacity="0.6"/>
    <path d="M0 420 Q 300 380 600 400 T 1200 410 T 1500 395 L 1500 500 L 0 500 Z" fill="#0F172A" opacity="0.8"/>
</svg>
SVG;
        $headerPath = "branding/{$company->id}/header.svg";
        $disk->put($headerPath, $header);

        $company->update([
            'logo_path' => $logoPath,
            'header_image_path' => $headerPath,
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
            'body_text' => "VANCOUVER, BC. Aurelian Gold Resources Corp (TSX-V: AUR) is pleased to announce results from hole AUR-26-001 at its 100%-owned Big Sky project in Nevada's Walker Lane trend. The hole returned 12.4 g/t Au over 28m, including 24.6 g/t Au over 8m, within the previously announced Hanging Wall Zone.\n\n\"This intercept confirms the continuity we hoped to see along the structural trend,\" said CEO Sarah Chen. \"We are accelerating Phase II drilling to test the strike extension to the north.\"",
            'body_html' => '<p>VANCOUVER, BC. Aurelian Gold Resources Corp...</p>',
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
            'body_text' => 'VANCOUVER, BC. Aurelian Gold Resources Corp (TSX-V: AUR) has closed its previously announced non-brokered private placement, raising aggregate gross proceeds of C$8,000,000 through the issuance of 17,777,777 units at C$0.45 per unit.',
            'body_html' => '<p>VANCOUVER, BC. Aurelian Gold...</p>',
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
            'body_text' => 'VANCOUVER, BC. Aurelian Gold Resources Corp (TSX-V: AUR) has commenced the Phase II drill program at its Big Sky gold project, Nevada.',
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
            'title' => 'Aurelian Gold Q1 2026 corporate update: land position expanded to 8,400 ha',
            'body_text' => 'VANCOUVER, BC. Aurelian Gold Resources Corp (TSX-V: AUR) reports Q1 2026 corporate updates. Strategic staking has increased the Big Sky land package to 8,400 hectares.',
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
                'body_of_work_summary' => 'Sinclair specializes in Nevada and western US gold/silver coverage, with strong focus on the Walker Lane and Carlin trends. Generally constructive on high-grade discoveries; tends to follow up on companies he\'s flagged earlier in a quarter.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
            'lutz' => Author::firstOrCreate(['slug' => 'kerry-lutz-demo'], [
                'name' => 'Kerry Lutz',
                'bio' => 'Host of Mining Stock Education podcast.',
                'x_handle' => '@kerrylutz',
                'body_of_work_summary' => 'Long-running mining podcast host; books CEOs of small-cap explorers, with a strong bias toward sub-$50M market cap stories with defined catalyst timelines.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
            'taggart' => Author::firstOrCreate(['slug' => 'adam-taggart-demo'], [
                'name' => 'Adam Taggart',
                'bio' => 'Founder of Crux Investor; macro-focused podcast host.',
                'x_handle' => '@adam_taggart',
                'body_of_work_summary' => 'Macro-focused host who weaves commodity thesis into company interviews. Bullish on gold, lithium, and uranium throughout 2026.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
            'cook' => Author::firstOrCreate(['slug' => 'brent-cook-demo'], [
                'name' => 'Brent Cook',
                'bio' => 'Geologist and exploration-focused newsletter writer.',
                'x_handle' => '@brent_cook',
                'body_of_work_summary' => 'Veteran geologist whose newsletter scrutinizes drill results closely. Cares about geology, not promotion.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
            'day' => Author::firstOrCreate(['slug' => 'adrian-day-demo'], [
                'name' => 'Adrian Day',
                'bio' => 'Resource fund manager and newsletter writer.',
                'x_handle' => '@adrian_day',
                'body_of_work_summary' => 'Veteran resource investor focused on royalty companies and developers near production. Bullish on gold long-term but skeptical of early-stage explorers.',
                'body_of_work_updated_at' => now()->subDays(2),
            ]),
        ];
    }

    /** @return array<string, PublicationItem> */
    private function seedPublicationItems(array $authors): array
    {
        $sources = $this->resolveSources();

        $items = [];

        $items['sinclair_drill_watch'] = $this->makeItem($sources['mining_com'], $authors['sinclair'], [
            'title' => 'Nevada drill season heats up: five names to watch this quarter',
            'url' => 'https://mining.example/nevada-drill-season-watch-2026',
            'published_at' => now()->subDays(4),
            'body_text' => 'With drill rigs across the Walker Lane and Carlin trends spinning, this quarter looks like one of the strongest in a decade for Nevada gold juniors.',
            'extracted_topics' => ['nevada', 'gold', 'walker-lane', 'drill-results', 'juniors'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => ['Nevada', 'Walker Lane'], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['sinclair_walker_lane'] = $this->makeItem($sources['mining_com'], $authors['sinclair'], [
            'title' => 'Walker Lane: another gold rush?',
            'url' => 'https://mining.example/walker-lane-gold-rush',
            'published_at' => now()->subDays(35),
            'body_text' => 'The Walker Lane structural belt is having a moment.',
            'extracted_topics' => ['walker-lane', 'gold', 'nevada', 'analysis'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => ['Nevada', 'Walker Lane'], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['lutz_nevada_bull'] = $this->makeItem($sources['mse_podcast'], $authors['lutz'], [
            'title' => 'Why Nevada gold is the most underpriced asset (with CEO Mark Reynolds)',
            'url' => 'https://mse.example/episode-nevada-gold-reynolds',
            'published_at' => now()->subDays(8),
            'body_text' => 'In this episode, host Kerry Lutz speaks with CEO Mark Reynolds about why Nevada-focused gold explorers are trading at depression-era valuations.',
            'extracted_topics' => ['nevada', 'gold', 'valuations', 'ceo-interview'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => ['Nevada'], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['lutz_reading_drills'] = $this->makeItem($sources['mse_podcast'], $authors['lutz'], [
            'title' => 'Reading drill results: what to look for',
            'url' => 'https://mse.example/episode-reading-drill-results',
            'published_at' => now()->subDays(40),
            'body_text' => 'Lutz walks listeners through how to evaluate JMC drill results.',
            'extracted_topics' => ['drill-results', 'education', 'gold'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'informational',
        ]);

        $items['taggart_macro_gold'] = $this->makeItem($sources['crux'], $authors['taggart'], [
            'title' => 'Macro setup for gold in H2 2026',
            'url' => 'https://crux.example/macro-gold-h2-2026',
            'published_at' => now()->subDays(12),
            'body_text' => 'The macro setup for H2 2026 looks constructive for gold.',
            'extracted_topics' => ['gold', 'macro', 'h2-2026', 'thesis'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['cook_drill_decoded'] = $this->makeItem($sources['cook_newsletter'], $authors['cook'], [
            'title' => 'Drill results decoded: what 12 g/t over 28m really means',
            'url' => 'https://cook.example/drill-results-decoded',
            'published_at' => now()->subDays(15),
            'body_text' => 'A wider intercept at moderate-to-high grade tells you something fundamentally different than a high-grade narrow vein.',
            'extracted_topics' => ['drill-results', 'geology', 'education', 'gold'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'informational',
        ]);

        $items['day_cautious'] = $this->makeItem($sources['day_newsletter'], $authors['day'], [
            'title' => "Why I'm cautious on most JMCs this cycle",
            'url' => 'https://day.example/cautious-on-jmcs',
            'published_at' => now()->subDays(3),
            'body_text' => 'Despite the constructive macro, most junior miners will not deliver returns this cycle.',
            'extracted_topics' => ['jmc', 'cautious', 'contrarian'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'contrarian',
        ]);

        $items['day_royalty'] = $this->makeItem($sources['day_newsletter'], $authors['day'], [
            'title' => 'Royalty companies for the gold cycle',
            'url' => 'https://day.example/royalty-companies-gold-cycle',
            'published_at' => now()->subDays(50),
            'body_text' => 'If you want gold exposure without exploration risk, the royalty model is the cleanest expression.',
            'extracted_topics' => ['royalty', 'gold', 'thesis'],
            'entities' => ['commodities' => ['gold'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['lithium_glut'] = $this->makeItem($sources['mining_com'], $authors['sinclair'], [
            'title' => 'Lithium glut: supply story still overstated',
            'url' => 'https://mining.example/lithium-glut-overstated',
            'published_at' => now()->subDays(20),
            'body_text' => 'Lithium prices have stabilized, but the supply glut narrative remains overstated.',
            'extracted_topics' => ['lithium', 'supply', 'contrarian'],
            'entities' => ['commodities' => ['lithium'], 'jurisdictions' => ['Australia'], 'companies' => []],
            'stance' => 'contrarian',
        ]);

        $items['copper_thesis'] = $this->makeItem($sources['crux'], $authors['taggart'], [
            'title' => 'Copper bull thesis revisited',
            'url' => 'https://crux.example/copper-bull-revisited',
            'published_at' => now()->subDays(28),
            'body_text' => 'The copper bull case continues to look strong.',
            'extracted_topics' => ['copper', 'macro'],
            'entities' => ['commodities' => ['copper'], 'jurisdictions' => [], 'companies' => []],
            'stance' => 'bullish',
        ]);

        $items['cook_off_topic'] = $this->makeItem($sources['cook_newsletter'], $authors['cook'], [
            'title' => 'A South American silver story worth a second look',
            'url' => 'https://cook.example/south-american-silver',
            'published_at' => now()->subDays(22),
            'body_text' => 'Sometimes the most overlooked stories are hiding in plain sight.',
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
        // firstOrCreate keyed on the (stable, demo-specific) URL so multiple
        // demo seeds against the same install share the same publication
        // items instead of stamping duplicates. The matches table fans out
        // per-team, so sharing the source content is fine.
        return PublicationItem::firstOrCreate(
            ['url' => $attrs['url']],
            [
                'source_id' => $source->id,
                'author_id' => $author->id,
                'external_guid' => 'demo-'.Str::random(16),
                'title' => $attrs['title'],
                'body_text' => $attrs['body_text'],
                'published_at' => $attrs['published_at'],
                'analysis_status' => PublicationItem::ANALYSIS_DONE,
                'analyzed_at' => $attrs['published_at']->copy()->addMinutes(15),
                'extracted_topics' => $attrs['extracted_topics'],
                'extracted_entities' => $attrs['entities'],
                'stance' => $attrs['stance'],
            ]
        );
    }

    private function seedClaims(array $items, array $authors): void
    {
        ExtractedClaim::create([
            'publication_item_id' => $items['day_cautious']->id,
            'author_id' => $authors['day']->id,
            'claim_text' => 'Most JMCs will underperform gold this cycle.',
            'topic' => 'gold',
            'stance' => 'contrarian',
            'verified_outcome' => ExtractedClaim::VERIFIED_UNVERIFIABLE,
            'verified_at' => now()->subDays(2),
            'verification_evidence' => ['reason' => 'qualitative claim'],
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
            'verification_evidence' => ['evidence' => '3 new Walker Lane discoveries publicly announced in Q1 2026.'],
        ]);
    }

    /** @return array<string, MatchRecord> */
    private function seedMatches(Company $company, array $releases, array $items, array $authors, User $user): array
    {
        $drillRelease = $releases['drill'];
        $placementRelease = $releases['placement'];

        MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['sinclair_drill_watch']->id,
            'author_id' => $authors['sinclair']->id,
            'score' => 0.87,
            'rationale_md' => 'Sinclair published "Nevada drill season heats up: five names to watch" 4 days ago, calling out Walker Lane high-grade plays as the most interesting setups of the quarter. Aurelian\'s fresh 12.4 g/t Au over 28m intercept at Big Sky lands squarely in that frame.',
            'suggested_angle_md' => 'Email Sinclair directly with the AUR-26-001 intercept summary plus a brief on what Phase II will test. Offer Sarah Chen for a 15-minute call. Keep it factual.',
            'citations' => [
                ['publication_item_id' => $items['sinclair_drill_watch']->id, 'quote' => 'Nevada drill season heats up: five names to watch this quarter'],
            ],
            'status' => MatchRecord::STATUS_NEW,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['lutz_nevada_bull']->id,
            'author_id' => $authors['lutz']->id,
            'score' => 0.81,
            'rationale_md' => 'Lutz aired "Why Nevada gold is the most underpriced asset" 8 days ago. The entire episode argues precisely the thesis Aurelian\'s intercept now reinforces.',
            'suggested_angle_md' => 'Pitch a CEO interview slotted as a follow-up to the Reynolds episode.',
            'citations' => [
                ['publication_item_id' => $items['lutz_nevada_bull']->id, 'quote' => 'Why Nevada gold is the most underpriced asset'],
            ],
            'status' => MatchRecord::STATUS_NEW,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['cook_drill_decoded']->id,
            'author_id' => $authors['cook']->id,
            'score' => 0.76,
            'rationale_md' => 'Cook\'s "Drill results decoded" column dissected exactly the type of intercept Aurelian just reported. He explicitly praised this geometry over narrow-vein high-grade.',
            'suggested_angle_md' => 'Send Cook the long-section showing continuity along strike. Skip the executive bio.',
            'citations' => [
                ['publication_item_id' => $items['cook_drill_decoded']->id, 'quote' => 'Drill results decoded: what 12 g/t over 28m really means'],
            ],
            'status' => MatchRecord::STATUS_NEW,
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(9),
        ]);

        $contactedMatch = MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $placementRelease->id,
            'publication_item_id' => $items['taggart_macro_gold']->id,
            'author_id' => $authors['taggart']->id,
            'score' => 0.79,
            'rationale_md' => 'Taggart\'s "Macro setup for gold in H2 2026" argues real rates rolling over make H2 a constructive tape for explorers with cash. Aurelian just closed C$8M.',
            'suggested_angle_md' => 'Pitch Sarah for a 20-minute Crux Investor interview tied to the macro setup.',
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
            'notes_md' => 'Sent Adam an email referencing his macro piece. Awaiting reply.',
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        $placedMatch = MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['sinclair_walker_lane']->id,
            'author_id' => $authors['sinclair']->id,
            'score' => 0.83,
            'rationale_md' => 'Sinclair\'s "Walker Lane: another gold rush?" piece framed the belt as a structural revival story. Aurelian\'s intercept extends precisely that narrative.',
            'suggested_angle_md' => 'Send Sinclair the intercept + structural map showing on-strike continuity with the trend names from his Walker Lane piece.',
            'citations' => [
                ['publication_item_id' => $items['sinclair_walker_lane']->id, 'quote' => 'Walker Lane: another gold rush?'],
            ],
            'status' => MatchRecord::STATUS_PLACED,
            'placement_url' => 'https://mining.example/aurelian-big-sky-walker-lane-2026',
            'placement_title' => "Aurelian's Big Sky intercept adds to Walker Lane revival story",
            'placement_description' => 'TSX-V junior Aurelian Gold delivered a 12.4 g/t Au over 28m intercept at its Nevada Big Sky project.',
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
            'notes_md' => 'Sent the intercept summary + on-strike continuity map to Sinclair.',
            'created_at' => now()->subDays(7),
            'updated_at' => now()->subDays(7),
        ]);
        MatchEvent::create([
            'match_id' => $placedMatch->id,
            'user_id' => $user->id,
            'event_type' => MatchRecord::STATUS_PLACED,
            'notes_md' => 'Article published, Sinclair tied us directly into his ongoing Walker Lane trend coverage.',
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $dismissedMatch = MatchRecord::create([
            'company_id' => $company->id,
            'press_release_id' => $drillRelease->id,
            'publication_item_id' => $items['day_cautious']->id,
            'author_id' => $authors['day']->id,
            'score' => 0.62,
            'rationale_md' => 'Day covers gold but his "Why I\'m cautious on most JMCs" piece is explicitly contrarian on exactly the type of story Aurelian represents.',
            'suggested_angle_md' => 'Better to revisit Day when Aurelian has a PEA in hand.',
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
            'notes_md' => "Adrian's publicly cautious on exploration-stage JMCs right now.",
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        return [
            'contacted' => $contactedMatch,
            'placed' => $placedMatch,
        ];
    }

    /**
     * Build out the company's Media Library: logo + header (added via branding),
     * 3 project photos (gradient SVG placeholders), 2 pull quotes, 1 link.
     */
    private function seedMediaAssets(Company $company, array $releases): void
    {
        $disk = Storage::disk('public');

        // Three project photos: each a gradient SVG with a label.
        $projectPhotos = [
            ['title' => 'Big Sky Project, Nevada', 'subtitle' => 'Walker Lane · 8,400 ha', 'gradient' => ['#B8501D', '#7C2D12'], 'tags' => ['big-sky', 'nevada', 'gold']],
            ['title' => 'AUR-26-001 Drill Core', 'subtitle' => '12.4 g/t Au over 28m', 'gradient' => ['#92400E', '#451A03'], 'tags' => ['drill-results', 'gold', 'big-sky']],
            ['title' => 'Hanging Wall Zone Section', 'subtitle' => 'Structural interpretation', 'gradient' => ['#1E40AF', '#1E293B'], 'tags' => ['geology', 'walker-lane', 'big-sky']],
        ];

        foreach ($projectPhotos as $i => $photo) {
            $svg = $this->makeProjectPhotoSvg($photo['title'], $photo['subtitle'], $photo['gradient'][0], $photo['gradient'][1]);
            $path = "media/{$company->id}/project-".($i + 1).'.svg';
            $disk->put($path, $svg);

            MediaAsset::create([
                'company_id' => $company->id,
                'type' => MediaAsset::TYPE_IMAGE,
                'name' => $photo['title'],
                'description' => $photo['subtitle'],
                'file_path' => $path,
                'mime_type' => 'image/svg+xml',
                'size_bytes' => strlen($svg),
                'width_px' => 1200,
                'height_px' => 800,
                'tags' => $photo['tags'],
                'is_active' => true,
                'source' => MediaAsset::SOURCE_MANUAL,
                'sort_order' => $i,
            ]);
        }

        // Two pull quotes — one manual, one auto-extracted from the drill PR.
        MediaAsset::create([
            'company_id' => $company->id,
            'type' => MediaAsset::TYPE_QUOTE,
            'name' => 'Chen on Walker Lane continuity',
            'quote_text' => 'This intercept confirms the continuity we hoped to see along the structural trend. We are accelerating Phase II drilling to test the strike extension to the north.',
            'quote_attribution' => 'Sarah Chen, CEO',
            'tags' => ['drill-results', 'big-sky', 'walker-lane'],
            'is_active' => true,
            'source' => MediaAsset::SOURCE_PRESS_RELEASE,
            'source_press_release_id' => $releases['drill']->id,
        ]);

        MediaAsset::create([
            'company_id' => $company->id,
            'type' => MediaAsset::TYPE_QUOTE,
            'name' => 'Chen on the AUR thesis',
            'quote_text' => 'Walker Lane has been underexplored for two decades. The structural fingerprints that worked at Round Mountain extend south through Big Sky. We are testing exactly those settings.',
            'quote_attribution' => 'Sarah Chen, CEO',
            'tags' => ['walker-lane', 'thesis', 'big-sky'],
            'is_active' => true,
            'source' => MediaAsset::SOURCE_MANUAL,
        ]);

        // External link: a fictional analyst note.
        MediaAsset::create([
            'company_id' => $company->id,
            'type' => MediaAsset::TYPE_LINK,
            'name' => 'Haywood initiation note (March 2026)',
            'description' => 'Buy rating, C$1.20 target.',
            'url' => 'https://example.com/haywood-aur-initiation-march-2026.pdf',
            'tags' => ['analyst', 'gold'],
            'is_active' => true,
            'source' => MediaAsset::SOURCE_MANUAL,
        ]);
    }

    private function makeProjectPhotoSvg(string $title, string $subtitle, string $colorA, string $colorB): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice">
    <defs>
        <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="{$colorA}"/>
            <stop offset="100%" stop-color="{$colorB}"/>
        </linearGradient>
    </defs>
    <rect width="1200" height="800" fill="url(#bg)"/>
    <path d="M0 580 Q 200 540 400 560 T 800 570 T 1200 555 L 1200 800 L 0 800 Z" fill="#000" opacity="0.25"/>
    <path d="M0 650 Q 250 620 500 635 T 1000 630 T 1200 620 L 1200 800 L 0 800 Z" fill="#000" opacity="0.35"/>
    <text x="60" y="690" font-family="sans-serif" font-size="48" font-weight="700" fill="white">{$title}</text>
    <text x="60" y="730" font-family="sans-serif" font-size="22" font-weight="500" fill="white" opacity="0.85">{$subtitle}</text>
</svg>
SVG;
    }

    /**
     * Create a published one-pager on the placed Sinclair match, with curated
     * assets, a personal note, and a handful of view rows over the last few
     * days so the engagement panel shows real numbers.
     */
    private function seedOnePager(MatchRecord $placedMatch, Company $company, User $user): OnePager
    {
        $onePager = $placedMatch->ensureOnePager($user->id);

        $onePager->update([
            'note_md' => "Hey Robert,\n\nFollow-up to your Walker Lane piece. Hole AUR-26-001 returned 12.4 g/t Au over 28m at our Big Sky project, on-strike with the structural trend you named. Happy to chat through the long-section anytime; Sarah is around all week.\n\nThanks,\nLee",
            'status' => OnePager::STATUS_PUBLISHED,
            'published_at' => now()->subDays(7),
        ]);

        // Select all images, both quotes, and the analyst link. Skip nothing.
        $assetIds = $company->mediaAssets()->where('is_active', true)->pluck('id')->all();
        $sync = [];
        foreach ($assetIds as $i => $id) {
            $sync[$id] = ['sort_order' => $i];
        }
        $onePager->assets()->sync($sync);

        // Generate 14 view rows across the past 6 days with ~5 unique IPs.
        $uniqueHashes = [
            hash('sha256', 'demo-ip-1'.config('app.key')),
            hash('sha256', 'demo-ip-2'.config('app.key')),
            hash('sha256', 'demo-ip-3'.config('app.key')),
            hash('sha256', 'demo-ip-4'.config('app.key')),
            hash('sha256', 'demo-ip-5'.config('app.key')),
        ];

        $userAgents = [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_5 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Edge/120.0',
        ];

        $viewCount = 14;
        for ($i = 0; $i < $viewCount; $i++) {
            OnePagerView::create([
                'one_pager_id' => $onePager->id,
                'ip_hash' => $uniqueHashes[$i % count($uniqueHashes)],
                'user_agent' => $userAgents[$i % count($userAgents)],
                'referrer' => $i % 3 === 0 ? 'https://mail.google.com/' : null,
                'viewed_at' => now()->subDays(6)->addHours($i * 9 + 2),
            ]);
        }

        $onePager->update([
            'view_count' => $viewCount,
            'unique_view_count' => count($uniqueHashes),
            'last_viewed_at' => now()->subHours(4),
        ]);

        return $onePager;
    }

    /**
     * Three watches to show off the Observatory:
     *
     *   - Walker Lane (location, literal+LLM) — busy watch with a mix of
     *     confirmed / pending / rejected hits so the LLM verdict UI has
     *     content to render.
     *   - Carlin Trend (location, literal) — fewer hits, all unconfirmed
     *     because the watch is literal-only.
     *   - Newmont (company, literal) — zero hits, shows the empty-state
     *     panel on the watch detail page.
     *
     * Enables the team's llm_observatory_enabled flag so the LLM-mode
     * watch actually runs in LLM mode for the demo. Flip it off in
     * Filament to see the downgrade/upgrade UI.
     */
    private function seedObservatory(Team $team, Company $company, User $user): void
    {
        $team->forceFill(['llm_observatory_enabled' => true])->save();

        // Compose-and-scan helper.
        $build = function (array $attrs) use ($company, $user): Watch {
            return Watch::create(array_merge([
                'company_id' => $company->id,
                'created_by_user_id' => $user->id,
                'is_active' => true,
            ], $attrs));
        };

        $scanner = app(WatchScanner::class);

        // ── Walker Lane: busy + LLM-confirmed ────────────────────────
        $walkerLane = $build([
            'name' => 'Walker Lane',
            'kind' => Watch::KIND_LOCATION,
            'terms' => ['Walker Lane', 'Walker Lane trend', 'Walker Lane belt'],
            'mode' => Watch::MODE_LITERAL_LLM,
        ]);
        $scanner->scan($walkerLane);

        // Annotate the hits with a realistic mix instead of dispatching the
        // real LLM job — demo seeding shouldn't depend on an API key.
        $walkerHits = $walkerLane->hits()->orderBy('id')->get();
        foreach ($walkerHits as $i => $hit) {
            // First two confirmed, third left pending so the UI shows both states.
            if ($i < 2) {
                $hit->forceFill([
                    'confirmed_by_llm' => true,
                    'llm_reasoning' => 'Snippet clearly references the Walker Lane structural belt in the geological sense.',
                ])->save();
            }
        }

        // ── Carlin Trend: literal-only ───────────────────────────────
        $carlin = $build([
            'name' => 'Carlin Trend',
            'kind' => Watch::KIND_LOCATION,
            'terms' => ['Carlin Trend', 'Carlin trends', 'Carlin'],
            'mode' => Watch::MODE_LITERAL,
        ]);
        $scanner->scan($carlin);

        // ── Newmont: no hits expected (none of the seed items mention it).
        $build([
            'name' => 'Newmont',
            'kind' => Watch::KIND_COMPANY,
            'terms' => ['Newmont', 'Newmont Mining', 'Newmont Goldcorp', 'NEM'],
            'mode' => Watch::MODE_LITERAL_LLM,
        ]);
    }

    private function printSummary(User $user, Team $team, Company $company, OnePager $onePager): void
    {
        $this->newLine();
        $this->info('Demo data seeded.');
        $this->newLine();
        $this->line("Team:      {$team->name}");
        $this->line("Owner:     {$user->name} <{$user->email}>");
        $this->line("Company:   {$company->name} ({$company->ticker}.{$company->exchange})");
        $this->newLine();

        $this->line('What you can see in the workspace:');
        $this->line('  - <fg=yellow>/dashboard</> — overview with hero opportunity');
        $this->line('  - <fg=yellow>/dashboard/companies/'.$company->id.'</> — 4 press releases');
        $this->line('  - <fg=yellow>/dashboard/companies/'.$company->id.'/library</> — media library with 6 assets');
        $this->line('  - <fg=yellow>/dashboard/companies/'.$company->id.'/branding</> — logo + header + colors');
        $this->line('  - <fg=yellow>/dashboard/companies/'.$company->id.'/observatory</> — 3 watches, mix of LLM-confirmed + literal');
        $this->line('  - <fg=yellow>/dashboard/matches</> — 3 new, 1 contacted, 1 placed, 1 dismissed');
        $this->line('  - <fg=yellow>/dashboard/wins</> — the placed match');
        $this->line('  - <fg=cyan>/onepagers/'.$onePager->uuid.'</> — public one-pager (no auth)');
        $this->newLine();

        if ($user->email === 'demo@prcomet.test') {
            $this->line('Login: <fg=cyan>demo@prcomet.test</> / <fg=cyan>'.$this->option('password').'</>');
            $this->newLine();
        }
    }
}
