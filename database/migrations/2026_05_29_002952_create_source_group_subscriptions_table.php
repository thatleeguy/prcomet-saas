<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot recording which teams are subscribed to which source groups.
 *
 * Modelled with real columns (not a bare pivot) so we can record
 * subscription provenance — when it started, who granted it, whether
 * it's a complimentary access flag (free trial / loaner) vs. a paid
 * subscription, and an optional expires_at for time-boxed access.
 *
 * Billing is intentionally out of scope for v0 — these columns are
 * the seeds that a future billing surface will read from.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('source_group_subscriptions')) {
            return;
        }

        Schema::create('source_group_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_group_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_complimentary')->default(false);
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('granted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'source_group_id'], 'subs_unique_team_group');
            $table->index(['team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_group_subscriptions');
    }
};
