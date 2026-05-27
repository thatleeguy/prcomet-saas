<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracted_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();

            $table->text('claim_text');
            $table->string('topic')->nullable()->index();
            $table->string('stance')->nullable();

            // Forward-looking predictions get a predicted_outcome + timeframe.
            // Outcome verification fills the verified_* fields later.
            $table->text('predicted_outcome')->nullable();
            $table->string('timeframe')->nullable();

            $table->string('verified_outcome')->nullable(); // correct|incorrect|partial|unverifiable
            $table->timestamp('verified_at')->nullable();
            $table->json('verification_evidence')->nullable();

            $table->timestamps();

            $table->index('verified_outcome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracted_claims');
    }
};
