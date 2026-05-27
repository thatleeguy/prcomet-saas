<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publication_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();

            $table->string('external_guid')->index();
            $table->string('url');
            $table->string('title');
            $table->longText('body_text')->nullable();
            $table->longText('transcript')->nullable(); // for podcasts/youtube
            $table->timestamp('published_at')->nullable()->index();

            // Phase 5 analysis output
            $table->string('analysis_status')->default('pending');
            $table->timestamp('analyzed_at')->nullable();
            $table->json('extracted_entities')->nullable();
            $table->json('extracted_topics')->nullable();
            $table->string('stance')->nullable();
            $table->json('embedding')->nullable();

            $table->timestamps();

            $table->unique(['source_id', 'external_guid']);
            $table->index(['source_id', 'published_at']);
            $table->index('analysis_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_items');
    }
};
