<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;

/**
 * Wipe the database and rebuild it with a known set of users + full demo data.
 *
 * Three users land on three different team states so we can demo every
 * customer surface without hopping accounts in production:
 *
 *  - manager@prcomet.com  → super-admin, owns a team with full demo data
 *    seeded (Aurelian + matches + library + one-pager + Observatory with
 *    LLM mode enabled). Use this to sweep the app from the operator side
 *    and to impersonate the others from /admin.
 *
 *  - customer@prcomet.com → regular user, owns a team with full demo data
 *    AND the LLM observatory upgrade ON. Mirrors a paying customer's view
 *    — useful for screenshots / sales decks.
 *
 *  - prospect@prcomet.com → regular user, owns an empty active team. No
 *    demo data, no upgrades. Mirrors what a brand-new account sees after
 *    activation. The empty-states across the app render against this user.
 *
 * Usage:
 *   php artisan app:bootstrap-fresh           # asks for confirmation
 *   php artisan app:bootstrap-fresh --force   # no prompt; safe for CI
 */
class BootstrapFresh extends Command
{
    protected $signature = 'app:bootstrap-fresh
        {--force : Skip the destructive-action confirmation prompt}';

    protected $description = 'Drop every table, run all migrations, and seed a known set of demo users + workspaces';

    /** Default seeded users — single source of truth for credentials. */
    private const USERS = [
        [
            'name' => 'Manager',
            'email' => 'manager@prcomet.com',
            'password' => 'fty2026!@#',
            'is_admin' => true,
            'seed_demo' => true,
            'llm_observatory' => true,
            'team_name' => 'PrComet Operations',
        ],
        [
            'name' => 'Customer Demo',
            'email' => 'customer@prcomet.com',
            'password' => 'password',
            'is_admin' => false,
            'seed_demo' => true,
            'llm_observatory' => true,
            'team_name' => 'Aurelian Gold IR',
        ],
        [
            'name' => 'Prospect Demo',
            'email' => 'prospect@prcomet.com',
            'password' => 'password',
            'is_admin' => false,
            'seed_demo' => false,
            'llm_observatory' => false,
            'team_name' => 'New Customer',
        ],
    ];

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will drop every table and rebuild the database. Continue?', false)) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $this->info('Dropping tables and re-running migrations…');
        Artisan::call('migrate:fresh', ['--force' => true], $this->output);

        $this->info('Seeding global mining source corpus…');
        Artisan::call('db:seed', ['--class' => 'MiningSourceSeeder', '--force' => true]);

        foreach (self::USERS as $spec) {
            $this->createUser($spec);
        }

        // Seed demo data onto every user flagged for it. Done after all users
        // exist so the global corpus is shared and demo seeds don't trip on
        // each other.
        foreach (self::USERS as $spec) {
            if ($spec['seed_demo']) {
                $this->info("Seeding Aurelian demo on {$spec['email']}…");
                Artisan::call('app:seed-demo', [
                    '--email' => $spec['email'],
                ]);
            }
        }

        $this->printSummary();

        return self::SUCCESS;
    }

    /**
     * Create a user, their personal team, and pin them as the team owner. Sets
     * the LLM observatory upgrade where requested. The team is marked active
     * with a generous seat limit so demo seeds don't trip the cap.
     */
    private function createUser(array $spec): void
    {
        $this->info("Creating {$spec['email']}…");

        DB::transaction(function () use ($spec) {
            $user = new User;
            $user->forceFill([
                'name' => $spec['name'],
                'email' => $spec['email'],
                'password' => Hash::make($spec['password']),
                'email_verified_at' => now(),
                'is_admin' => $spec['is_admin'],
            ])->save();

            $team = $user->ownedTeams()->save(Jetstream::newTeamModel()->forceFill([
                'user_id' => $user->id,
                'name' => $spec['team_name'],
                'personal_team' => true,
                'is_active' => true,
                'max_companies' => 5,
                'activated_at' => now(),
                'llm_observatory_enabled' => $spec['llm_observatory'],
            ]));

            $user->forceFill(['current_team_id' => $team->id])->save();
        });
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('Bootstrap complete.');
        $this->newLine();
        $this->line('Seeded users:');
        foreach (self::USERS as $spec) {
            $flags = collect([
                $spec['is_admin'] ? 'admin' : null,
                $spec['seed_demo'] ? 'demo data' : 'empty team',
                $spec['llm_observatory'] ? 'LLM observatory ON' : 'LLM observatory off',
            ])->filter()->implode(' · ');

            $this->line(sprintf(
                '  <fg=cyan>%-26s</> %-18s  [%s]',
                $spec['email'],
                $spec['password'],
                $flags,
            ));
        }

        $this->newLine();
        $this->line('Sign in at <fg=yellow>/login</>. The admin panel is at <fg=yellow>/admin</>.');
        $this->line('From /admin/users you can impersonate the other test accounts to see what they see.');
        $this->newLine();
    }
}
