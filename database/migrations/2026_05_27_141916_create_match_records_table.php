<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('press_release_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publication_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();

            $table->float('score'); // 0..1
            $table->longText('rationale_md');
            $table->longText('suggested_angle_md');
            $table->json('citations')->nullable(); // [{url, quote}, ...]

            $table->string('status')->default('new'); // new|saved|contacted|dismissed|placed

            $table->timestamps();

            $table->unique(['press_release_id', 'publication_item_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
