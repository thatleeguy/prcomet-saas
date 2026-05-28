<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('one_pager_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('one_pager_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['one_pager_id', 'media_asset_id']);
            $table->index(['one_pager_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('one_pager_assets');
    }
};
