<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
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

        // Auth::login($user);

        return redirect()->route('login')->with('success', 'Compte créé avec succès !');
    }
}
