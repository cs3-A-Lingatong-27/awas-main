<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendWeeklyTeacherSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-weekly-teacher-summaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
public function handle()
{
    $nextWeekStart = now()->addWeek()->startOfWeek();
    $nextWeekEnd = now()->addWeek()->endOfWeek();

    // Get all teachers/admins
    $teachers = \App\Models\User::whereIn('role', ['teacher', 'admin'])->get();

    foreach ($teachers as $teacher) {
        // Get grades assigned to this teacher
        $grades = is_array($teacher->assigned_grades) ? $teacher->assigned_grades : json_decode($teacher->assigned_grades, true) ?? [];

        // Temporary change for testing
$assessments = \App\Models\Assessment::whereIn('grade_level', $grades)->get();

        if ($assessments->isNotEmpty()) {
            \Illuminate\Support\Facades\Mail::to($teacher->email)
                ->send(new \App\Mail\WeeklyAssessmentSummary($assessments, $teacher->name));
        }
    }
}
}
