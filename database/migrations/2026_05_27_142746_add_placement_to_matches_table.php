<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            // Outbound attribution: when a match converts into a real placement
            // (interview, article, mention), the user pastes the URL and we
            // fetch title/description for the wins dashboard.
            $table->string('placement_url')->nullable()->after('citations');
            $table->string('placement_title')->nullable()->after('placement_url');
            $table->text('placement_description')->nullable()->after('placement_title');
            $table->timestamp('placement_published_at')->nullable()->after('placement_description');
            $table->timestamp('placement_fetched_at')->nullable()->after('placement_published_at');
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'placement_url',
                'placement_title',
                'placement_description',
                'placement_published_at',
                'placement_fetched_at',
            ]);
        });
    }
};
