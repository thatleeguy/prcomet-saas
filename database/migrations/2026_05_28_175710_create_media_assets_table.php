<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Asset taxonomy. Each type renders differently on the one-pager:
            // image/logo show inline; pdf/link become download/visit tiles;
            // quote renders as a styled blockquote with attribution.
            $table->string('type'); // image|logo|header|pdf|quote|link

            $table->string('name');
            $table->text('description')->nullable();

            // File-backed assets (image/logo/header/pdf).
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('width_px')->nullable();
            $table->unsignedSmallInteger('height_px')->nullable();

            // URL-backed assets (link).
            $table->string('url')->nullable();

            // Quote assets.
            $table->text('quote_text')->nullable();
            $table->string('quote_attribution')->nullable();

            // Curation / discovery.
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);

            // Provenance — was this added manually, or auto-ingested from a PR?
            $table->string('source')->default('manual'); // manual|press_release
            $table->foreignId('source_press_release_id')->nullable()
                ->constrained('press_releases')->nullOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_assets');
    }
};
