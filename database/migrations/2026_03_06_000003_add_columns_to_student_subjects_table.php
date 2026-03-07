<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('student_subjects', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('grade_level')->nullable()->after('user_id');
            $table->string('subject_name')->nullable()->after('grade_level');
            $table->string('subject_type', 30)->nullable()->after('subject_name');
            $table->index(['user_id', 'grade_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_subjects', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'grade_level']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['grade_level', 'subject_name', 'subject_type']);
        });
    }
};
