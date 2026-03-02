<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\UserInvitation;
use Spatie\Permission\Models\Role;

class UserInvitationMail extends Mailable
{
    use SerializesModels;

    public $invitation;

    /**
     * Create a new message instance.
     */
    public function __construct(UserInvitation $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation à rejoindre PSSP IMPACT+',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $roleName = 'Membre';
        if ($this->invitation->role_id) {
            $roleName = optional(Role::find($this->invitation->role_id))->name ?? 'Utilisateur';
        }

        return new Content(
            view: 'emails.user-invitation',
            with: [
                'token' => $this->invitation->token,
                'email' => $this->invitation->email,
                'role' => $roleName,
                'siteName' => optional($this->invitation->site)->site_name,
                'isSiteResponsable' => (bool) $this->invitation->assign_as_site_responsable,
                'inviterName' => $this->invitation->inviter ? $this->invitation->inviter->firstname : 'L\'administrateur',
            ],
        );
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
