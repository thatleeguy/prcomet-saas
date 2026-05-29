<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Promote newsroom_subscribers from a per-company capture list to a
 * single PrComet-owned identity table.
 *
 * Before: one row per (company, email) pair. Each customer "owned"
 * their subscribers; nothing to bridge across companies.
 *
 * After: one row per email globally. Subscriber identities sit on
 * the PrComet network; companies attach to them via the
 * newsroom_subscriptions pivot. The strategic value: PrComet owns
 * the relationship, customers get amplification, subscribers get
 * one digest instead of N pings.
 *
 * Migration is destructive (drops the old table) because:
 *   - The current rows are demo / test data only — no production
 *     loss to worry about.
 *   - Keeping the old shape around as a "v1" while introducing the
 *     new one would create two sources of truth that nothing
 *     would reconcile.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('newsroom_subscribers');

        Schema::create('newsroom_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('email_hashed', 64);
            $table->string('token', 64);
            $table->string('name')->nullable();

            // instant | daily | weekly. Default weekly because it's
            // the cadence that's most defensible against churn ("one
            // email a week, never sold") while still being useful.
            $table->string('cadence', 16)->default('weekly');

            // Double opt-in: row exists immediately on subscribe but
            // dispatch is gated on confirmed_at being non-null.
            $table->timestamp('confirmed_at')->nullable();

            // Global unsubscribe: once set, no further sends across
            // any company. Individual company opt-outs live on the
            // pivot row.
            $table->timestamp('unsubscribed_at')->nullable();

            // Last digest sent — only used for cadence=daily|weekly.
            // The digest job finds new publishes since this timestamp
            // across every company the subscriber follows.
            $table->timestamp('last_digest_sent_at')->nullable();

            $table->string('source_ref')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamps();

            $table->unique('email_hashed');
            $table->unique('token');
            $table->index(['cadence', 'unsubscribed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsroom_subscribers');

        // Restore the original per-company shape so down() leaves the
        // schema in a runnable state even though we never expect
        // anyone to actually revert this.
        Schema::create('newsroom_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('email_hashed', 64);
            $table->string('source_ref')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'email_hashed'], 'newsroom_subs_unique');
            $table->index('company_id');
        });
    }
};
