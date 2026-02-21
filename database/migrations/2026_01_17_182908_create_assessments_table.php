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
    Schema::create('assessments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The Teacher
        $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
        $table->string('section');      // e.g., 'Diamond', 'Einstein'
        $table->integer('grade_level'); // e.g., 7, 11
        $table->string('type');         // Formative, Summative, etc.
        $table->string('title');
        $table->dateTime('scheduled_at'); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
