<?php

use Database\Seeders\ArticleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed the initial "The Angle" articles on deploy. This runs as part of
     * the standard `migrate --force` step, so no extra Forge config is needed.
     * ArticleSeeder is idempotent (firstOrCreate by slug), so it is safe even
     * if it ever reruns, and it won't clobber edits made later in the admin.
     */
    public function up(): void
    {
        (new ArticleSeeder())->run();
    }

    public function down(): void
    {
        // Content seed — nothing to roll back.
    }
};
