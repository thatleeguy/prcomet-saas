<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('personal_team');
            $table->unsignedInteger('max_companies')->default(0)->after('is_active');
            $table->timestamp('activated_at')->nullable()->after('max_companies');
            $table->foreignId('activated_by_id')->nullable()->after('activated_at')
                ->constrained('users')->nullOnDelete();
            $table->text('billing_notes')->nullable()->after('activated_by_id');
            $table->timestamp('paid_through_at')->nullable()->after('billing_notes');

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['activated_by_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'is_active',
                'max_companies',
                'activated_at',
                'activated_by_id',
                'billing_notes',
                'paid_through_at',
            ]);
        });
    }
};
