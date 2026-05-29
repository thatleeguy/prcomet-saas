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
 *
 * MySQL note: the sibling migration that creates newsroom_subscriptions
 * has the SAME timestamp and sorts BEFORE this one alphabetically,
 * so it runs first and creates a FK from newsroom_subscriptions to
 * newsroom_subscribers. MySQL refuses to drop the parent of a
 * referenced FK, so we drop the FK here, swap the table, and re-add
 * the FK afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->detachPivotFk();

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

        $this->reattachPivotFk();
    }

    public function down(): void
    {
        $this->detachPivotFk();

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

        $this->reattachPivotFk();
    }

    /**
     * Detach the FK from newsroom_subscriptions before dropping the
     * parent table. Safe to call when the pivot or the FK doesn't
     * exist yet (fresh install path).
     */
    private function detachPivotFk(): void
    {
        if (! Schema::hasTable('newsroom_subscriptions')) {
            return;
        }

        try {
            Schema::table('newsroom_subscriptions', function (Blueprint $table) {
                $table->dropForeign(['newsroom_subscriber_id']);
            });
        } catch (\Throwable $e) {
            // FK either doesn't exist (already dropped) or has a
            // non-default name. Either way, dropping the parent
            // table will surface a real failure if there's still an
            // active reference.
        }
    }

    /**
     * Re-attach the FK on the pivot to the freshly-created identity
     * table. Wrapped in try/catch so a re-deploy that already has
     * the FK in place doesn't fail.
     */
    private function reattachPivotFk(): void
    {
        if (! Schema::hasTable('newsroom_subscriptions')) {
            return;
        }
        if (! Schema::hasColumn('newsroom_subscriptions', 'newsroom_subscriber_id')) {
            return;
        }

        try {
            Schema::table('newsroom_subscriptions', function (Blueprint $table) {
                $table->foreign('newsroom_subscriber_id')
                    ->references('id')->on('newsroom_subscribers')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // FK already present (re-deploy idempotency) — nothing
            // to do.
        }
    }
};
