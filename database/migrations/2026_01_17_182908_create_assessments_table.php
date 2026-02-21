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
        // The teacher who created it
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
        $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
        $table->foreignId('room_id')->nullable()->constrained()->onDelete('cascade');
        
        $table->string('section'); // IMPORTANT: e.g., 'Einstein', 'Newton'
        $table->string('type');    // Formative, Summative, etc.
        $table->string('title');
        $table->text('description')->nullable();
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
