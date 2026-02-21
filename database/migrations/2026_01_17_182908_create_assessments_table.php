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
        $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
        $table->foreignId('room_id')->nullable()->constrained()->onDelete('cascade');
        $table->string('type'); // Formative, Alternative, etc.
        $table->string('title');
        $table->text('description')->nullable();
        $table->dateTime('scheduled_at'); // This stores BOTH date and time
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
