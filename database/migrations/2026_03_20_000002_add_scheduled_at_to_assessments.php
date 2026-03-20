<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'scheduled_at')) {
                $table->dateTime('scheduled_at')->nullable()->after('due_date');
            }
        });

        if (Schema::hasColumn('assessments', 'scheduled_at') && Schema::hasColumn('assessments', 'due_date')) {
            DB::table('assessments')
                ->whereNull('scheduled_at')
                ->update(['scheduled_at' => DB::raw('due_date')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};
