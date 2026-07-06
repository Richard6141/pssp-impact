<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * E-mail de bienvenue envoyé à la création d'un compte.
 * Si $setPasswordUrl est fourni (compte créé par un administrateur),
 * l'utilisateur est invité à définir lui-même son mot de passe.
 */
class WelcomeMail extends Mailable
{
    public function __construct(
        public User $user,
        public ?string $setPasswordUrl = null,
        public ?string $roleName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur PSSP IMPACT+ - Votre compte a été créé',
        );
    }

    public function content(): Content
    {
        $with = [
            'user' => $this->user,
            'setPasswordUrl' => $this->setPasswordUrl,
            'roleName' => $this->roleName,
            'loginUrl' => route('login'),
        ];

        return new Content(
            view: 'emails.welcome',
            text: 'emails.text.welcome',
            with: $with,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
