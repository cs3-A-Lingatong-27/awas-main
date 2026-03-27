<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSubject extends Model
{
    protected $fillable = [
        'user_id',
        'grade_level',
        'subject_name',
        'subject_type',
    ];

    /**
     * Relationship: student subject record belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
