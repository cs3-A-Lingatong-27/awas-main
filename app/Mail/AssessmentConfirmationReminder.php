<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssessmentConfirmationReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $teacherName,
        public string $assessmentTitle,
        public string $assessmentType,
        public string $assessmentDate
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Assessment Confirmation Needed');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.assessment-confirmation-reminder');
    }
}
