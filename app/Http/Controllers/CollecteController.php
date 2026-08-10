<?php

namespace App\Http\Controllers;

use App\Models\Collecte;
use App\Models\Incident;
use App\Models\TypeDechet;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\HasVisibleSiteIds;

class CollecteController extends Controller
{
    use HasVisibleSiteIds;
    private function applyCollecteVisibility($query)
    {
        $user = auth()->user();

        // Agent collecte : uniquement ses propres collectes
        if ($user->hasRole('Agent collecte')) {
            $query->where('agent_id', $user->user_id);
            return $query;
        }

        // Responsable site : collectes des sites dont il est responsable
        if ($user->hasRole('Responsable site')) {
            $query->whereHas('site', function ($q) use ($user) {
                $q->where('responsable', $user->user_id);
            });
            return $query;
        }

        // Tout rôle avec data.own_site_only : uniquement les sites rattachés à l'utilisateur
        if ($user->hasPermissionTo('data.own_site_only')) {
            $siteIds = $user->sites()->pluck('sites.site_id')->toArray();
            if ($user->site_id) {
                $siteIds[] = $user->site_id;
            }
            $siteIds = array_unique(array_filter($siteIds));

            if (!empty($siteIds)) {
                $query->whereIn('site_id', $siteIds);
            }
            return $query;
        }

        // data.all_sites, Coordonnateur, Administrateur, Super Admin → voient tout
        return $query;
    }



    private function ensureSiteAllowed(string $siteId): void
    {
        $user = auth()->user();
        if ($user->hasRole('Responsable site')) {
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
    public function index(Request $request)
    {
        $query = Collecte::with(['typeDechet', 'agent', 'site', 'validation'])
            ->withCount('factures')
            ->orderBy('date_collecte', 'desc');

        $this->applyCollecteVisibility($query);

        // Recherche côté serveur (multi-pages)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_collecte', 'like', "%{$search}%")
                    ->orWhereHas('site', fn ($s) => $s->where('site_name', 'like', "%{$search}%"))
                    ->orWhereHas('typeDechet', fn ($t) => $t->where('libelle', 'like', "%{$search}%"))
                    ->orWhereHas('agent', function ($a) use ($search) {
                        $a->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%");
                    });
            });
        }

        $collectes = $query->paginate(10)->withQueryString();

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
        if ($user->hasRole('Responsable site')) {
            $sitesQuery->where('responsable', $user->user_id);
        }
        $sites = $sitesQuery->get();
        $agents = User::all();

        return view('collectes.create', compact('types', 'sites', 'agents'));
    }

    /**
     * Regles de validation du poids : on refuse explicitement une saisie plus
     * precise que ce que la colonne peut stocker, plutot que de laisser MySQL
     * l'arrondir en silence (retour terrain du 10/08/2026).
     */
    private function poidsRules(): array
    {
        return [
            'required',
            'numeric',
            'min:0',
            'decimal:0,' . Collecte::POIDS_DECIMALES,
        ];
    }

    /**
     * Les claviers mobiles francophones produisent "12,5" : on normalise en
     * "12.5" avant validation pour ne pas rejeter une saisie legitime.
     */
    private function normaliserPoids(Request $request): void
    {
        if (is_string($request->input('poids'))) {
            $request->merge([
                'poids' => str_replace([' ', ','], ['', '.'], $request->input('poids')),
            ]);
        }
    }

    /**
     * Enregistrement en base
     */
    public function store(Request $request)
    {
        $this->normaliserPoids($request);

        $request->validate([
            'poids' => $this->poidsRules(),
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
        if ($user->hasRole('Responsable site')) {
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
        $this->normaliserPoids($request);

        $request->validate([
            'date_collecte' => 'required|date',
            'poids' => $this->poidsRules(),
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

        if ($collecte->signature_responsable_site || $collecte->isValid) {
            return redirect()
                ->route('collectes.index')
                ->with('error', 'Suppression impossible: cette collecte a d�j� �t� valid�e.');
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
