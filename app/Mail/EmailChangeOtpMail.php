<?php

namespace App\Mail;

use App\Models\PendingEmailChange;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PendingEmailChange $pendingEmailChange)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Ashvalian email change OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-otp',
        );
    }
}
