<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    // Afficher tous les sites
    public function index()
    {
        $sites = Site::with('responsableUser')->latest()->get();
        return view('sites.index', compact('sites'));
    }

    // Formulaire de création
    public function create()
    {
        $users = User::all(); // pour choisir un responsable
        return view('sites.create', compact('users'));
    }

    // Sauvegarde en BDD
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_departement' => 'required|string|max:255',
            'site_commune' => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'responsable' => 'nullable|exists:users,user_id',
        ]);

        Site::create([
            'site_id' => Str::uuid(),
            'site_name' => $validated['site_name'],
            'site_departement' => $validated['site_departement'],
            'site_commune' => $validated['site_commune'],
            'localisation' => $validated['localisation'],
            'longitude' => $validated['longitude'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'responsable' => $validated['responsable'] ?? null,
        ]);

        return redirect()->route('sites.index')->with('success', 'Site ajouté avec succès.');
    }

    // Voir un site
    public function show($id)
    {
        $site = Site::with('responsableUser')->findOrFail($id);
        return view('sites.show', compact('site'));
    }

    // Formulaire d’édition
    public function edit($id)
    {
        $site = Site::findOrFail($id);
        $users = User::all();
        return view('sites.edit', compact('site', 'users'));
    }

    // Mise à jour
    public function update(Request $request, $id)
    {
        $site = Site::findOrFail($id);

        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_departement' => 'required|string|max:255',
            'site_commune' => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'responsable' => 'nullable|exists:users,user_id',
        ]);

        $site->update($validated);

        return redirect()->route('sites.index')->with('success', 'Site mis à jour avec succès.');
    }

    // Suppression
    public function destroy($id)
    {
        $site = Site::findOrFail($id);
        $site->delete();

        return redirect()->route('sites.index')->with('success', 'Site supprimé avec succès.');
    }
}
