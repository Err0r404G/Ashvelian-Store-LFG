<?php

namespace App\Mail;

use App\Models\PendingPasswordReset;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PendingPasswordReset $pendingPasswordReset)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Ashvalian password reset OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp',
        );
    }
}
