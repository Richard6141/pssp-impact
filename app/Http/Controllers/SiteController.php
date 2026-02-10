<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    private function getVisibleSiteIds()
    {
        $user = auth()->user();
        $siteIds = $user->sites()->pluck('sites.site_id')->all();
        $responsableSiteIds = Site::where('responsable', $user->user_id)->pluck('site_id')->all();

        $siteIds = array_merge($siteIds, $responsableSiteIds);

        if (!empty($user->site_id)) {
            $siteIds[] = $user->site_id;
        }

        return array_values(array_unique(array_filter($siteIds)));
    }

    private function applySiteVisibility($query)
    {
        $user = auth()->user();
        if ($user->hasRole('Agent collecte')) {
            $query->whereIn('site_id', $this->getVisibleSiteIds());
        } elseif ($user->hasRole('Responsable site')) {
            $query->where('responsable', $user->user_id);
        }

        return $query;
    }

    private function ensureSiteAllowed(string $siteId): void
    {
        $user = auth()->user();
        if ($user->hasRole('Agent collecte')) {
            $allowed = $this->getVisibleSiteIds();
            if (!in_array($siteId, $allowed, true)) {
                abort(403);
            }
        } elseif ($user->hasRole('Responsable site')) {
            $isAllowed = Site::where('site_id', $siteId)
                ->where('responsable', $user->user_id)
                ->exists();
            if (!$isAllowed) {
                abort(403);
            }
        }
    }

    // Afficher tous les sites
    public function index()
    {
        $query = Site::with('responsableUser')->latest();
        $this->applySiteVisibility($query);
        $sites = $query->get();
        return view('sites.index', compact('sites'));
    }

    // Formulaire de création
    public function create()
    {
        // Récupérer uniquement les utilisateurs ayant le rôle "Agent Collecte"
        $users = User::role('Agent collecte')->get();

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
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
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

        // Redirection conditionnelle
        if ($request->input('redirect_to') === 'configuration') {
            return redirect()->route('configuration')->with('success', 'Site ajouté avec succès.');
        }

        return redirect()->route('sites.index')->with('success', 'Site ajouté avec succès.');
    }

    // Voir un site
    public function show($id)
    {
        $this->ensureSiteAllowed($id);
        $site = Site::with('responsableUser')->findOrFail($id);
        return view('sites.show', compact('site'));
    }

    // Formulaire d’édition
    public function edit($id)
    {
        $this->ensureSiteAllowed($id);
        $site = Site::findOrFail($id);
        $users = User::role('Agent collecte')->get();
        return view('sites.edit', compact('site', 'users'));
    }

    // Mise à jour
    public function update(Request $request, $id)
    {
        $this->ensureSiteAllowed($id);
        $site = Site::findOrFail($id);

        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_departement' => 'required|string|max:255',
            'site_commune' => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
            'responsable' => 'nullable|exists:users,user_id',
        ]);

        $site->update($validated);

        // Redirection conditionnelle
        if ($request->input('redirect_to') === 'configuration') {
            return redirect()->route('configuration')->with('success', 'Site mis à jour avec succès.');
        }

        return redirect()->route('sites.index')->with('success', 'Site mis à jour avec succès.');
    }

    // Suppression
    public function destroy(Request $request, $id)
    {
        $this->ensureSiteAllowed($id);
        $site = Site::findOrFail($id);
        $site->delete();

        // Redirection conditionnelle
        if ($request->input('redirect_to') === 'configuration') {
            return redirect()->route('configuration')->with('success', 'Site supprimé avec succès.');
        }

        return redirect()->route('sites.index')->with('success', 'Site supprimé avec succès.');
    }
}
