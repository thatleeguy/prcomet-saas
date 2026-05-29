<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Subscriber × Company pivot.
 *
 * Soft-detach via unsubscribed_at instead of hard-deleting the row,
 * so the subscriber can re-subscribe without losing their history
 * (and so the customer's "lifetime subscribers" metric stays
 * accurate even when people drop off temporarily).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsroom_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsroom_subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamp('subscribed_at');
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['newsroom_subscriber_id', 'company_id'], 'newsroom_subs_pivot_unique');
            $table->index(['company_id', 'unsubscribed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsroom_subscriptions');
    }
};
