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
    Schema::table('users', function (Blueprint $table) {
        // Change from string/int to json to hold multiple values
        $table->json('assigned_grades')->nullable();   // e.g., [7, 8]
        $table->json('assigned_subjects')->nullable(); // e.g., ["Math", "Science"]
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('json', function (Blueprint $table) {
            //
        });
    }
};
