<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional newsroom-specific header image. Falls back to the
 * company's main header_image_path when unset, so customers who
 * don't care about per-surface customisation can ignore this field
 * entirely and still get a consistent brand across one-pagers and
 * the newsroom.
 *
 * Useful for companies that want the newsroom to feel evergreen
 * (CEO portrait, project landscape, facility shot) while letting
 * individual one-pagers carry release-specific imagery.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('companies', 'newsroom_header_image_path')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->string('newsroom_header_image_path')->nullable()->after('header_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('newsroom_header_image_path');
        });
    }
};
