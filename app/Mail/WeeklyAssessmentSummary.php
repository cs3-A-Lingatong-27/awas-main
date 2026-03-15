<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyAssessmentSummary extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $groupedSummary,
        public $handledClasses,
        public $classesWithAssessments,
        public $assignedSubjects,
        public $scopeAssessmentList,
        public $teacherName,
        public $weekStart,
        public $weekEnd
    ) {}

public function envelope(): Envelope {
    return new Envelope(subject: 'Weekly Assessment Summary (Monday-Friday)');
}

public function content(): Content {
    return new Content(view: 'emails.weekly-summary');
}
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
