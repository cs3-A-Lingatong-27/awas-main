<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assessment extends Model
{
    // Allows these fields to be saved to the database
   protected $fillable = [
        'subject_id', 
        'room_id', 
        'type', 
        'title', 
        'description', 
        'scheduled_at', 
        'grade_level' // <--- CRITICAL: Add this
    ];

    // Link to the Subject
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    // Link to the Room
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
