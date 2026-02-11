<?php

namespace App\Services;

use App\Models\UserInvitation;
use App\Models\User;
use App\Mail\UserInvitationMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class UserInvitationService
{
    /**
     * Créer et envoyer une invitation
     */
    public function createInvitation(string $email, int $roleId, User $inviter)
    {
        // Vérifier si l'email existe déjà dans les utilisateurs actifs
        if (User::where('email', $email)->exists()) {
            throw new Exception("Cet email est déjà utilisé par un compte existant.");
        }

        // Vérifier s'il y a déjà une invitation active pour cet email
        $existingInvitation = UserInvitation::where('email', $email)
            ->active()
            ->first();

        if ($existingInvitation) {
            // On peut soit renvoyer une erreur, soit renvoyer l'invitation existante
            // Ici, on va mettre à jour le token et renvoyer
            $existingInvitation->update([
                'token' => Str::random(32),
                'expires_at' => now()->addHours(48),
                'role_id' => $roleId, // Mise à jour du rôle si changé
                'inviter_id' => $inviter->user_id,
            ]);
            
            $invitation = $existingInvitation;
        } else {
            // Créer une nouvelle invitation
            $invitation = UserInvitation::create([
                'email' => $email,
                'token' => Str::random(32),
                'role_id' => $roleId,
                'inviter_id' => $inviter->user_id,
                'expires_at' => now()->addHours(48),
            ]);
        }

        // Envoyer l'email
        try {
            Mail::to($email)->queue(new UserInvitationMail($invitation));
        } catch (Exception $e) {
            // Log l'erreur mais ne pas bloquer
            \Log::error("Erreur lors de l'envoi de l'invitation à $email: " . $e->getMessage());
            throw new Exception("L'invitation a été créée mais l'email n'a pas pu être envoyé. Erreur: " . $e->getMessage());
        }

        return $invitation;
    }

    /**
     * Accepter une invitation et créer l'utilisateur
     */
    public function acceptInvitation(string $token, array $userData, string $password)
    {
        $invitation = UserInvitation::where('token', $token)
            ->active()
            ->first();

        if (!$invitation) {
            throw new Exception("Invitation invalide ou expirée.");
        }

        return DB::transaction(function () use ($invitation, $userData, $password) {
            // Créer l'utilisateur
            $user = User::create([
                'firstname' => $userData['firstname'],
                'lastname' => $userData['lastname'],
                'username' => $userData['username'],
                'email' => $invitation->email,
                'password' => Hash::make($password),
                'isActive' => true,
                'email_verified_at' => now(),
            ]);

            // Assigner le rôle
            if ($invitation->role_id) {
                $role = \Spatie\Permission\Models\Role::find($invitation->role_id);
                if ($role) {
                    // Si vous utilisez une table de pivot personnalisée ou spatie
                    $user->assignRole($role->name);
                }
            }

            // Marquer l'invitation comme terminée
            $invitation->update([
                'registered_at' => now(),
            ]);

            return $user;
        });
    }

    /**
     * Annuler une invitation
     */
    public function cancelInvitation(int $id)
    {
        $invitation = UserInvitation::findOrFail($id);
        $invitation->delete();
    }

    /**
     * Renvoyer une invitation
     */
    public function resendInvitation(int $id)
    {
        $invitation = UserInvitation::findOrFail($id);
        
        if ($invitation->registered_at) {
            throw new Exception("Cette invitation a déjà été acceptée.");
        }

        // Régénérer token et expiration
        $invitation->update([
            'token' => Str::random(32),
            'expires_at' => now()->addHours(48),
        ]);

        // Renvoyer l'email
        Mail::to($invitation->email)->queue(new UserInvitationMail($invitation));

        return $invitation;
    }
}
