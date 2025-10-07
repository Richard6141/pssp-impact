<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Liste des emails autorisés à avoir le rôle Super Admin
     */
    private const SUPER_ADMIN_EMAILS = [
        'richardsomasse@gmail.com',
        'richardsalanon@hotmail.com',
        'salanonluc7@gmail.com',
    ];

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // Validation des champs avec messages personnalisés en français
        $validatedData = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'localisation' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'isActive' => ['nullable', 'boolean'],
        ], [
            // Messages personnalisés en français
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
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
        ]);

        // Création de l'utilisateur
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

        // Attribution du rôle en fonction de l'email
        $this->assignUserRole($user, $validatedData['email']);

        // Log de la création du compte avec le rôle assigné
        \Log::info('Nouveau compte créé', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->first() ?? 'Aucun rôle',
            'is_super_admin' => $user->hasRole('Super Admin')
        ]);

        // Auth::login($user);

        return redirect()->route('login')->with('success', 'Compte créé avec succès !');
    }

    /**
     * Assigner le rôle approprié à l'utilisateur en fonction de son email
     * UNIQUEMENT les emails dans SUPER_ADMIN_EMAILS reçoivent un rôle
     *
     * @param User $user
     * @param string $email
     * @return void
     */
    private function assignUserRole(User $user, string $email): void
    {
        try {
            // Normaliser l'email (en minuscules et sans espaces)
            $normalizedEmail = strtolower(trim($email));

            // Vérifier si l'email fait partie de la liste des Super Admins
            if (in_array($normalizedEmail, self::SUPER_ADMIN_EMAILS)) {
                // Assigner le rôle Super Admin UNIQUEMENT
                $user->assignRole('Super Admin');

                \Log::info('Rôle Super Admin assigné automatiquement', [
                    'user_id' => $user->id,
                    'email' => $email
                ]);
            } else {
                // AUCUN rôle n'est assigné automatiquement
                // Les rôles devront être assignés manuellement par un admin

                \Log::info('Compte créé sans rôle - Attribution manuelle requise', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'note' => 'Un administrateur doit assigner un rôle à cet utilisateur'
                ]);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, logger et continuer sans bloquer la création
            \Log::error('Erreur lors de l\'attribution du rôle', [
                'user_id' => $user->id,
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            // Optionnel : Vous pouvez aussi envoyer une notification aux admins
        }
    }

    /**
     * Vérifier si un email est autorisé comme Super Admin
     *
     * @param string $email
     * @return bool
     */
    public static function isSuperAdminEmail(string $email): bool
    {
        return in_array(strtolower(trim($email)), self::SUPER_ADMIN_EMAILS);
    }
}
