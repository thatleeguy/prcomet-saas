<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operator-managed backup ledger.
 *
 * Each row is a single snapshot — a zip archive on the configured
 * backup disk containing the database dump and (optionally) the
 * uploaded files. The DB driver gets recorded on each row so the
 * operator can tell at a glance which environment a backup came
 * from before downloading.
 *
 * The archive itself lives on disk; this table just describes it
 * (so the index page renders without touching the filesystem on
 * every request).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('backups')) {
            return;
        }

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('disk');
            $table->string('db_driver');
            $table->boolean('includes_files')->default(false);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
