<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Digest cadence — 'daily' | 'weekly' | 'off'. Default daily so
            // new signups get pulled back in without a manual opt-in step.
            $table->string('digest_frequency')->default('daily')->after('is_admin');
            $table->timestamp('digest_sent_at')->nullable()->after('digest_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['digest_frequency', 'digest_sent_at']);
        });
    }
};
