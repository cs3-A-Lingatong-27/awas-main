<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('confirmation_status')->default('scheduled')->after('scheduled_at');
            $table->timestamp('confirmation_requested_at')->nullable()->after('confirmation_status');
            $table->timestamp('conducted_at')->nullable()->after('confirmation_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['confirmation_status', 'confirmation_requested_at', 'conducted_at']);
        });
    }
};
