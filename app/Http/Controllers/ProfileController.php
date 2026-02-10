<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Afficher la page de profil
     */
    public function show()
    {
        return view('profile.show');
    }

    /**
     * Mettre à jour les informations du profil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'localisation' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profil mis à jour avec succès !');
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')->with('success', 'Mot de passe mis à jour avec succès !');
    }

    /**
     * Mettre à jour les informations professionnelles
     */
    public function updateProfessional(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'availability_status' => ['required', 'in:available,busy,offline'],
            'service_communes' => ['nullable', 'string'],
            'specialties' => ['nullable', 'string'],
        ]);

        // Transformation des chaînes en tableaux (nettoyage des espaces)
        $serviceCommunes = $request->service_communes 
            ? array_values(array_filter(array_map('trim', explode(',', $request->service_communes))))
            : [];
            
        $specialties = $request->specialties 
            ? array_values(array_filter(array_map('trim', explode(',', $request->specialties))))
            : [];

        $user->update([
            'availability_status' => $validated['availability_status'],
            'service_communes' => $serviceCommunes,
            'specialties' => $specialties,
            'last_active_at' => now(),
        ]);

        return back()->with('success', 'Informations professionnelles mises à jour avec succès !');
    }
}
