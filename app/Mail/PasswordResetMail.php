<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetMail extends Mailable
{
    public function __construct(
        public string $resetUrl,
        public string $userEmail
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Réinitialisation de votre mot de passe - PSSP IMPACT+',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            text: 'emails.text.password-reset',
            with: [
                'resetUrl'  => $this->resetUrl,
                'userEmail' => $this->userEmail,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
