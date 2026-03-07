<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentGradeSection extends Model
{
    protected $fillable = [
        'user_id',
        'grade_level',
        'section',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

