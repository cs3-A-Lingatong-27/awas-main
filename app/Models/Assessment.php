<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Assessment extends Model
{
    protected $fillable = [
        'user_id',      // The teacher creating it
        'subject_id', 
        'room_id', 
        'section',      // Added for conflict detection
        'grade_level', 
        'type', 
        'title', 
        'description', 
        'due_date',
        'scheduled_at',
        'confirmation_status',
        'confirmation_requested_at',
        'conducted_at',
    ];

    /**
     * AWAS Conflict Detection Logic
     * Checks if a specific section already has assessments on a given date.
     */
    public static function getConflictCount(string $section, $dateTime): int
    {
        $date = Carbon::parse($dateTime)->toDateString();

        return self::where('section', $section)
            ->whereDate('scheduled_at', $date)
            ->count();
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    protected $casts = [
        'due_date' => 'datetime',
        'scheduled_at' => 'datetime',
        'confirmation_requested_at' => 'datetime',
        'conducted_at' => 'datetime',
    ];

    // Link to the Teacher/User who created the assessment
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
