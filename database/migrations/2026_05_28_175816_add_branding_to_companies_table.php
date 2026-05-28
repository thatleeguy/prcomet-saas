<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Branding bundle used by the one-pager rendering. Stored on the
            // Company (not the OnePager) so it stays consistent across every
            // page that company publishes — and changes propagate without
            // re-editing each one-pager.
            $table->string('logo_path')->nullable()->after('website');
            $table->string('header_image_path')->nullable()->after('logo_path');
            $table->string('accent_color', 16)->nullable()->after('header_image_path');
            $table->string('tagline')->nullable()->after('accent_color');
            $table->text('description_md')->nullable()->after('tagline');
            $table->string('press_contact_email')->nullable()->after('description_md');
            $table->json('social_links')->nullable()->after('press_contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'header_image_path',
                'accent_color',
                'tagline',
                'description_md',
                'press_contact_email',
                'social_links',
            ]);
        });
    }
};
