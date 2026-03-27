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

    /**
     * Create a reminder message with assessment metadata.
     */
    public function __construct(
        public string $teacherName,
        public string $assessmentTitle,
        public string $assessmentType,
        public string $assessmentDate
    ) {}

    /**
     * Define the email envelope details like subject.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Assessment Confirmation Needed');
    }

    /**
     * Define the email content view for the reminder.
     */
    public function content(): Content
    {
        return new Content(view: 'emails.assessment-confirmation-reminder');
    }
}
