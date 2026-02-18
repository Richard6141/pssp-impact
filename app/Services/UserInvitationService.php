<?php

namespace App\Services;

use App\Mail\UserInvitationMail;
use App\Models\Site;
use App\Models\User;
use App\Models\UserInvitation;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserInvitationService
{
    /**
     * Creer et envoyer une invitation.
     */
    public function createInvitation(
        string $email,
        int $roleId,
        User $inviter,
        ?string $siteId = null,
        bool $assignAsSiteResponsable = false
    ) {
        if (User::where('email', $email)->exists()) {
            throw new Exception("Cet email est deja utilise par un compte existant.");
        }

        $existingInvitation = UserInvitation::where('email', $email)
            ->active()
            ->first();

        if ($existingInvitation) {
            $existingInvitation->update([
                'token' => Str::random(32),
                'expires_at' => now()->addHours(48),
                'role_id' => $roleId,
                'site_id' => $siteId,
                'assign_as_site_responsable' => $assignAsSiteResponsable,
                'inviter_id' => $inviter->user_id,
            ]);

            $invitation = $existingInvitation;
        } else {
            $invitation = UserInvitation::create([
                'email' => $email,
                'token' => Str::random(32),
                'role_id' => $roleId,
                'site_id' => $siteId,
                'assign_as_site_responsable' => $assignAsSiteResponsable,
                'inviter_id' => $inviter->user_id,
                'expires_at' => now()->addHours(48),
            ]);
        }

        try {
            Mail::to($email)->queue(new UserInvitationMail($invitation));
        } catch (Exception $e) {
            \Log::error("Erreur lors de l'envoi de l'invitation a {$email}: {$e->getMessage()}");
            throw new Exception("L'invitation a ete creee mais l'email n'a pas pu etre envoye. Erreur: {$e->getMessage()}");
        }

        return $invitation;
    }

    /**
     * Accepter une invitation et creer l'utilisateur.
     */
    public function acceptInvitation(string $token, array $userData, string $password)
    {
        $invitation = UserInvitation::where('token', $token)
            ->active()
            ->first();

        if (!$invitation) {
            throw new Exception('Invitation invalide ou expiree.');
        }

        return DB::transaction(function () use ($invitation, $userData, $password) {
            $user = User::create([
                'firstname' => $userData['firstname'],
                'lastname' => $userData['lastname'],
                'username' => $userData['username'],
                'email' => $invitation->email,
                'password' => Hash::make($password),
                'site_id' => $invitation->site_id,
                'isActive' => true,
                'email_verified_at' => now(),
            ]);

            if ($invitation->role_id) {
                $role = \Spatie\Permission\Models\Role::find($invitation->role_id);
                if ($role) {
                    $user->assignRole($role->name);
                }
            }

            if (!empty($invitation->site_id)) {
                $user->sites()->sync([
                    $invitation->site_id => ['is_primary' => true],
                ]);

                if ((bool) $invitation->assign_as_site_responsable) {
                    Site::where('site_id', $invitation->site_id)->update([
                        'responsable' => $user->user_id,
                    ]);
                }
            }

            $invitation->update([
                'registered_at' => now(),
            ]);

            return $user;
        });
    }

    public function cancelInvitation(int $id)
    {
        $invitation = UserInvitation::findOrFail($id);
        $invitation->delete();
    }

    public function resendInvitation(int $id)
    {
        $invitation = UserInvitation::findOrFail($id);

        if ($invitation->registered_at) {
            throw new Exception('Cette invitation a deja ete acceptee.');
        }

        $invitation->update([
            'token' => Str::random(32),
            'expires_at' => now()->addHours(48),
        ]);

        Mail::to($invitation->email)->queue(new UserInvitationMail($invitation));

        return $invitation;
    }
}
