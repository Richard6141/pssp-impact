<?php

namespace App\Http\Controllers\Auth;

use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
            'localisation' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'isActive' => ['nullable', 'boolean'],
        ], [
            'firstname.required' => 'Le prénom est requis',
            'firstname.max' => 'Le prénom ne doit pas dépasser 255 caractères',
            'lastname.required' => 'Le nom est requis',
            'lastname.max' => 'Le nom ne doit pas dépasser 255 caractères',
            'username.required' => 'Le nom d\'utilisateur est requis',
            'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé',
            'username.max' => 'Le nom d\'utilisateur ne doit pas dépasser 255 caractères',
            'email.required' => 'L\'adresse email est requise',
            'email.email' => 'Veuillez entrer une adresse email valide',
            'email.unique' => 'Cette adresse email est déjà utilisée',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères',
            'password.required' => 'Le mot de passe est requis',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
        ]);

        $user = User::create([
            'firstname' => $validatedData['firstname'],
            'lastname' => $validatedData['lastname'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'localisation' => $validatedData['localisation'] ?? null,
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
            'isActive' => $validatedData['isActive'] ?? true,
        ]);

        $this->assignUserRole($user, $validatedData['email']);

        Log::info('Nouveau compte créé', [
            'user_id' => $user->id,
            'role' => $user->roles->pluck('name')->first() ?? 'Aucun rôle',
        ]);

        MailService::send(
            $user->email,
            new WelcomeMail($user, null, $user->roles->pluck('name')->first()),
            'self-registration'
        );

        return redirect()->route('login')->with('success', 'Compte créé avec succès ! Un e-mail de confirmation vous a été envoyé.');
    }

    private function assignUserRole(User $user, string $email): void
    {
        try {
            $normalizedEmail = strtolower(trim($email));
            $superAdminEmails = config('auth.super_admin_emails', []);

            if (!empty($superAdminEmails) && in_array($normalizedEmail, $superAdminEmails)) {
                $user->assignRole('Super Admin');
                Log::info('Rôle Super Admin assigné automatiquement', ['user_id' => $user->id]);
            } else {
                Log::info('Compte créé sans rôle - Attribution manuelle requise', ['user_id' => $user->id]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'attribution du rôle', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function isSuperAdminEmail(string $email): bool
    {
        $superAdminEmails = config('auth.super_admin_emails', []);
        return !empty($superAdminEmails) && in_array(strtolower(trim($email)), $superAdminEmails);
    }
}
