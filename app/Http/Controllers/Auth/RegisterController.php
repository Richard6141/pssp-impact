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
        // Validation des champs
        //dd($request);
        $validatedData = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // Attention : ajouter un champ password_confirmation dans le formulaire , 'min:8', 'confirmed'
            'localisation' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'isActive' => ['nullable', 'boolean'],
        ]);
        //dd($request);
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

        // Tu peux ici envoyer un email de vérification ou connecter directement l'utilisateur
        // Auth::login($user);

        return redirect()->route('login')->with('success', 'Compte créé avec succès !');
    }
}