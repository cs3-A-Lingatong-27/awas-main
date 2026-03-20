<?php

namespace App\Console\Commands;

use App\Mail\AssessmentConfirmationReminder;
use App\Models\Assessment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAssessmentConfirmationReminders extends Command
{
    protected $signature = 'app:send-assessment-confirmation-reminders';
    protected $description = 'Mark overdue assessments as pending and email teachers to confirm';

    public function handle(): int
    {
        $now = now();

        $assessments = Assessment::query()
            ->where('confirmation_status', 'scheduled')
            ->where('scheduled_at', '<', $now)
            ->with('teacher')
            ->get();

        foreach ($assessments as $assessment) {
            if (!$assessment->teacher) {
                continue;
            }

            $assessment->confirmation_status = 'pending';
            $assessment->confirmation_requested_at = $now;
            $assessment->save();

            Mail::to($assessment->teacher->email)->send(
                new AssessmentConfirmationReminder(
                    teacherName: $assessment->teacher->name,
                    assessmentTitle: $assessment->title,
                    assessmentType: $assessment->type,
                    assessmentDate: Carbon::parse($assessment->scheduled_at)->format('M d, Y g:i A')
                )
            );
        }

        $this->info('Assessment confirmation reminders sent: ' . $assessments->count());
        return self::SUCCESS;
    }
}
