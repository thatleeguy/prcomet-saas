<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('press_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('external_guid')->index();
            $table->string('source_url');
            $table->string('title');
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->timestamp('published_at')->nullable()->index();

            // Analysis pipeline state — populated in Phase 5.
            $table->string('analysis_status')->default('pending'); // pending|analyzing|done|failed
            $table->timestamp('analyzed_at')->nullable();
            $table->json('extracted_entities')->nullable();
            $table->json('extracted_topics')->nullable();
            $table->string('stance')->nullable();
            $table->json('key_claims')->nullable();
            $table->json('embedding')->nullable(); // array of floats; pgvector later

            $table->timestamps();

            $table->unique(['company_id', 'external_guid']);
            $table->index(['company_id', 'published_at']);
            $table->index('analysis_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('press_releases');
    }
};
