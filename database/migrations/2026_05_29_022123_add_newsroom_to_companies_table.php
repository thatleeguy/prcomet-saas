<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Public newsroom page for each company. Adds:
 *
 *   - slug: URL key for /newsroom/{slug}. Unique across the install.
 *   - newsroom_published: customer-controlled visibility toggle. When
 *     false the public route 404s; when true the page renders every
 *     one-pager the company has published.
 *
 * Backfills slug from existing names so the migration doesn't leave
 * any company unaddressable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('companies', 'slug')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });

            // Backfill slugs from existing names. Per-row to dedupe.
            $seen = [];
            foreach (DB::table('companies')->select('id', 'name')->get() as $row) {
                $base = Str::slug((string) $row->name);
                $candidate = $base;
                $i = 2;
                while (isset($seen[$candidate])) {
                    $candidate = $base.'-'.$i++;
                }
                $seen[$candidate] = true;
                DB::table('companies')->where('id', $row->id)->update(['slug' => $candidate]);
            }

            // Now lock in the unique constraint.
            Schema::table('companies', function (Blueprint $table) {
                $table->string('slug')->nullable(false)->change();
                $table->unique('slug');
            });
        }

        if (! Schema::hasColumn('companies', 'newsroom_published')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->boolean('newsroom_published')->default(false)->after('slug');
            });
        }
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'newsroom_published')) {
                $table->dropColumn('newsroom_published');
            }
            if (Schema::hasColumn('companies', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }
};
