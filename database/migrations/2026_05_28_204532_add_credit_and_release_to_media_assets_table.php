<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds attribution + media-release fields to assets.
 *
 *  - credit / credit_url: photographer or source attribution shown next to
 *    the image on the public one-pager.
 *  - uses_blanket_release: when true, the asset inherits the company's
 *    blanket release. When false, media_release_text / media_release_file_path
 *    on this row are used instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('credit')->nullable()->after('description');
            $table->string('credit_url')->nullable()->after('credit');
            $table->boolean('uses_blanket_release')->default(true)->after('credit_url');
            $table->text('media_release_text')->nullable()->after('uses_blanket_release');
            $table->string('media_release_file_path')->nullable()->after('media_release_text');
        });
    }

    public function down(): void
    {
        Schema::table('media_assets', function (Blueprint $table) {
            $table->dropColumn([
                'credit',
                'credit_url',
                'uses_blanket_release',
                'media_release_text',
                'media_release_file_path',
            ]);
        });
    }
};
