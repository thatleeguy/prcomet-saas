<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_source_id')->nullable()->constrained('sources')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->index();
            $table->text('bio')->nullable();
            $table->string('x_handle')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // LLM-generated rolling summary of the author's body of work — what
            // commodities/themes they cover, their stance, accuracy history.
            $table->longText('body_of_work_summary')->nullable();
            $table->timestamp('body_of_work_updated_at')->nullable();

            $table->json('tags')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
