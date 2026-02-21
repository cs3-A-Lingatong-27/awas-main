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
        // 1. Link to the Teacher (Crucial for line 73 of your controller)
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
        
        $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
        $table->foreignId('room_id')->nullable()->constrained()->onDelete('cascade');
        
        // 2. Added for AWAS logic
        $table->integer('grade_level'); 
        $table->string('section')->nullable(); 
        
        $table->string('type'); 
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
