<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();

            // Hybrid corpus: global = admin-curated and shared across all teams.
            // team = a team's private custom source.
            $table->string('scope')->default('global'); // global|team
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('type'); // publication|podcast|substack|x|youtube
            $table->string('name');
            $table->string('base_url')->nullable();
            $table->string('feed_url')->nullable();
            $table->string('ingest_strategy')->default('rss'); // rss|atom|api|manual
            $table->json('ingest_config')->nullable();
            $table->json('tags')->nullable(); // commodity / jurisdiction tags

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_ingested_at')->nullable();

            $table->timestamps();

            $table->index(['scope', 'is_active']);
            $table->index(['team_id', 'is_active']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};
