<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Liveness markers for background subsystems.
 *
 * Each `kind` ("scheduler", "queue") has one row that's overwritten
 * every time the subsystem checks in. The /manage System health page
 * compares last_at against expected cadence and reports red when a
 * subsystem hasn't checked in recently enough — that's how we know
 * the Forge scheduler or queue worker died without SSHing in.
 *
 * Updated frequently, never read in hot paths — small table, single
 * unique key on `kind`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('system_heartbeats')) {
            return;
        }

        Schema::create('system_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('kind')->unique();
            $table->timestamp('last_at');
            $table->text('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_heartbeats');
    }
};
