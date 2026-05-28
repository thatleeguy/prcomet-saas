<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blanket media release on the company — text + optional signed PDF.
 * Inherited by media library assets that don't have their own override.
 * Surfaces in the Branding screen so it lives alongside accent color,
 * tagline, and other company-wide presentation choices.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->text('blanket_media_release_text')->nullable();
            $table->string('blanket_media_release_file_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'blanket_media_release_text',
                'blanket_media_release_file_path',
            ]);
        });
    }
};
