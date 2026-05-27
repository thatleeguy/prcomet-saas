<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('ticker')->nullable();
            $table->string('exchange')->nullable();
            $table->string('website')->nullable();
            $table->string('rss_feed_url')->nullable();
            $table->json('secondary_feeds')->nullable();
            $table->string('ir_contact_name')->nullable();
            $table->string('ir_contact_email')->nullable();
            $table->string('ir_contact_phone')->nullable();
            $table->json('sector_tags')->nullable(); // commodities, jurisdictions
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_ingested_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
