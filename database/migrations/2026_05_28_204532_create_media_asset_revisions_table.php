<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * File-history sidecar for media_assets.
 *
 * When a user uploads a replacement file on an existing asset, we snapshot
 * the current file path + metadata into here BEFORE overwriting. Old files
 * stay on disk so "Make current" on a revision row just swaps the path back.
 * Cleanup of orphaned files is intentionally deferred — this is editorial
 * history, not a temp cache.
 *
 * `notes` is an optional one-liner the uploader can attach to explain what
 * changed ("rescaled for web", "replaced with final color-corrected version").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_asset_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width_px')->nullable();
            $table->unsignedInteger('height_px')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['media_asset_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_asset_revisions');
    }
};
