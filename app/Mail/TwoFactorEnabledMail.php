<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorEnabledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public array $recoveryCodes;

    public function __construct(User $user, array $recoveryCodes = [])
    {
        $this->user = $user;
        $this->recoveryCodes = $recoveryCodes;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Double authentification activee - PSSP IMPACT+',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor-enabled',
            with: [
                'user' => $this->user,
                'recoveryCodes' => $this->recoveryCodes,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
