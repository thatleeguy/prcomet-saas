<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('one_pager_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('one_pager_id')->constrained()->cascadeOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('viewed_at');

            $table->index(['one_pager_id', 'viewed_at']);
            $table->index(['one_pager_id', 'ip_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('one_pager_views');
    }
};
