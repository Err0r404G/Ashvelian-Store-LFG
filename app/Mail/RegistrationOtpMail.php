<?php

namespace App\Mail;

use App\Models\PendingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public PendingRegistration $pendingRegistration)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Ashvalian registration OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-otp',
        );
    }
}
