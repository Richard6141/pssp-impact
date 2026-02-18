<?php

namespace App\Http\Controllers;

use App\Models\Collecte;
use App\Models\Incident;
use App\Models\TypeDechet;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollecteController extends Controller
{
    private function applyCollecteVisibility($query)
    {
        $user = auth()->user();
        if ($user->hasRole('Agent collecte')) {
            $query->where('agent_id', $user->user_id);
        } elseif ($user->hasRole('Responsable site')) {
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('responsable', $user->user_id);
            });
        }

        return $query;
    }

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

    /**
     * Affichage de la liste
     */
    public function index()
    {
        $query = Collecte::with(['typeDechet', 'agent', 'site', 'validation'])
            ->withCount('factures')
            ->orderBy('date_collecte', 'desc');

        $this->applyCollecteVisibility($query);

        $collectes = $query->paginate(10);

        return view('collectes.index', compact('collectes'));
    }


    /**
     * Formulaire de création
     */
    public function create()
    {
        $types = TypeDechet::all();
        $sitesQuery = Site::query();
        $user = auth()->user();
        if ($user->hasRole('Agent collecte')) {
            $sitesQuery->whereIn('site_id', $this->getVisibleSiteIds());
        } elseif ($user->hasRole('Responsable site')) {
            $sitesQuery->where('responsable', $user->user_id);
        }
        $sites = $sitesQuery->get();
        $agents = User::all();

        return view('collectes.create', compact('types', 'sites', 'agents'));
    }

    /**
     * Enregistrement en base
     */
    public function store(Request $request)
    {
        $request->validate([
            'poids' => 'required|numeric|min:0',
            'type_dechet_id' => 'required|exists:type_dechets,type_dechet_id',
            'site_id' => 'required|exists:sites,site_id',
            // Validation conditionnelle pour l'incident
            'incident_description' => 'required_if:has_incident,1',
            'incident_date' => 'required_if:has_incident,1|nullable|date',
        ]);

        // Créer la collecte (sans les champs incident)
        $collecteData = $request->only([
            'poids',
            'type_dechet_id',
            'site_id',
            'signature_responsable_site',
            'isValid'
        ]);

        // Générer un UUID pour la collecte
        $collecteData['collecte_id'] = Str::uuid();
        $collecteData['date_collecte'] = now();


        // Forcer l'agent connecté comme agent_id
        $collecteData['agent_id'] = auth()->user()->user_id;
        $collecteData['numero_collecte'] = 'COL-' . strtoupper(Str::random(6));

        $this->ensureSiteAllowed($collecteData['site_id']);

        $collecte = Collecte::create($collecteData);

        // Si un incident doit être créé
        if ($request->has_incident == '1' && $request->filled('incident_description')) {
            Incident::create([
                'incident_id' => Str::uuid(),
                'collecte_id' => $collecte->collecte_id,
                'reported_by' => auth()->user()->user_id, // ✅ toujours l'utilisateur connecté
                'description' => $request->incident_description,
                'date_incident' => $request->incident_date,
                'statut' => 'ouvert'
            ]);
        }

        return redirect()->route('collectes.index')->with('success', 'Collecte enregistrée avec succès.');
    }


    /**
     * Afficher une collecte
     */
    public function show($id)
    {
        $collecte = $this->applyCollecteVisibility(
            Collecte::with(['typeDechet', 'agent', 'site', 'incident'])
        )->where('collecte_id', $id)->firstOrFail();

        return view('collectes.show', compact('collecte'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $collecte = $this->applyCollecteVisibility(
            Collecte::with('incident')
        )->where('collecte_id', $id)->firstOrFail();
        $types = TypeDechet::all();
        $sitesQuery = Site::query();
        $user = auth()->user();
        if ($user->hasRole('Agent collecte')) {
            $sitesQuery->whereIn('site_id', $this->getVisibleSiteIds());
        } elseif ($user->hasRole('Responsable site')) {
            $sitesQuery->where('responsable', $user->user_id);
        }
        $sites = $sitesQuery->get();
        $agents = User::all(); // Tous les agents pour permettre de changer

        return view('collectes.create', compact('collecte', 'types', 'sites', 'agents'));
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date_collecte' => 'required|date',
            'poids' => 'required|numeric|min:0',
            'type_dechet_id' => 'required|exists:type_dechets,type_dechet_id',
            'agent_id' => 'required|exists:users,user_id',
            'site_id' => 'required|exists:sites,site_id',
            // Validation conditionnelle pour l'incident
            'incident_description' => 'required_if:has_incident,1',
            'incident_date' => 'required_if:has_incident,1|nullable|date',
        ]);

        $collecte = $this->applyCollecteVisibility(
            Collecte::query()
        )->where('collecte_id', $id)->firstOrFail();

        // Mettre à jour les données de la collecte
        $collecteData = $request->only([
            'date_collecte',
            'poids',
            'type_dechet_id',
            'agent_id',
            'site_id',
            'signature_responsable_site',
            'isValid'
        ]);

        $this->ensureSiteAllowed($collecteData['site_id']);

        $collecte->update($collecteData);

        // Gestion de l'incident
        if ($request->has_incident == '1' && $request->filled('incident_description')) {
            // Si un incident existe déjà, le mettre à jour
            if ($collecte->incident) {
                $collecte->incident->update([
                    'description' => $request->incident_description,
                    'date_incident' => $request->incident_date,
                ]);
            } else {
                // Créer un nouvel incident
                Incident::create([
                    'incident_id' => Str::uuid(),
                    'collecte_id' => $collecte->collecte_id,
                    'reported_by' => $request->agent_id,
                    'description' => $request->incident_description,
                    'date_incident' => $request->incident_date,
                    'statut' => 'ouvert'
                ]);
            }
        } else {
            // Si has_incident = 0, supprimer l'incident existant s'il y en a un
            if ($collecte->incident) {
                $collecte->incident->delete();
            }
        }

        return redirect()->route('collectes.index')->with('success', 'Collecte mise à jour avec succès.');
    }

    /**
     * Suppression
     */
    public function destroy($id)
    {
        $collecte = $this->applyCollecteVisibility(
            Collecte::query()
        )->where('collecte_id', $id)->firstOrFail();

        if ($collecte->factures()->exists()) {
            return redirect()
                ->route('collectes.index')
                ->with('error', 'Suppression impossible: cette collecte est d�j� li�e � une facture.');
        }

        // L'incident sera supprim� automatiquement gr�ce au cascade dans la foreign key
        $collecte->delete();

        return redirect()->route('collectes.index')->with('success', 'Collecte supprimée avec succès.');
    }

    /**
     * Valider une collecte
     */
    public function validateCollecte(string $id)
    {
        $collecte = $this->applyCollecteVisibility(
            Collecte::query()
        )->where('collecte_id', $id)->firstOrFail();
        $collecte->update(['isValid' => true]);

        return back()->with('success', 'Collecte validée avec succès.');
    }



    /**
     * Invalider une collecte
     */
    public function invalidate(string $id)
    {
        $collecte = $this->applyCollecteVisibility(
            Collecte::query()
        )->where('collecte_id', $id)->firstOrFail();
        $collecte->update(['isValid' => false]);

        //return response()->json(['message' => 'Collecte invalidée', 'collecte' => $collecte]);
        return redirect()->route('collectes.index')->with('success', 'Collecte invalidée.');
    }
}
