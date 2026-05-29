<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogues / "source groups" — named bundles of sources that teams
 * subscribe to. A junior mining company subscribes to "Mining
 * Publications"; a cleantech IR team subscribes to "Clean Energy";
 * premium bundles (industry-specific newsletters, paywalled trade
 * journals) sell as add-ons.
 *
 * The fields here are operator-only metadata. Customers see a curated
 * view (name + description + count) on their settings panel.
 *
 * Soft deletes so that removing a catalogue from the operator UI
 * doesn't break previously-attached teams or matches downstream.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('source_groups')) {
            return;
        }

        Schema::create('source_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('monthly_price_cents')->nullable();
            $table->string('icon_emoji', 8)->nullable();
            $table->string('accent_color', 7)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['is_active', 'is_premium']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_groups');
    }
};
