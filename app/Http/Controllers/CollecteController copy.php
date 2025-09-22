<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Collecte;
use App\Models\Site;
use App\Models\Facture;
use App\Models\Rapport;
use Carbon\Carbon;

class CollecteController extends Controller
{
    // ================================
    // GESTION CRUD DE BASE
    // ================================

    /**
     * Afficher la liste de toutes les collectes
     */
    public function index(Request $request): JsonResponse
    {
        $collectes = Collecte::with(['site', 'agent', 'hopital', 'factures', 'rapports'])
            ->when($request->hopital_id, fn($q) => $q->where('hopital_id', $request->hopital_id))
            ->when($request->agent_id, fn($q) => $q->where('agent_id', $request->agent_id))
            ->when($request->site_id, fn($q) => $q->where('site_id', $request->site_id))
            ->orderBy('date_collecte', 'desc')
            ->paginate(15);

        return response()->json($collectes);
    }

    /**
     * Créer une nouvelle collecte
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_collecte' => 'required|date',
            'poids' => 'required|numeric', // ici ce n’est sûrement pas un UUID mais un nombre
            'nature_dbm' => 'required|integer',
            'hopital_id' => 'required|uuid|exists:sites,site_id',
            'signature_responsable_site' => 'boolean',
            'isValid' => 'boolean'
        ]);

        // Ajouter l'agent connecté
        $validated['agent_id'] = auth()->user()->user_id;

        // Créer la collecte
        $collecte = Collecte::create($validated);

        // Charger les relations utiles
        $collecte->load(['site', 'agent', 'hopital']);

        return response()->json($collecte, 201);
    }


    /**
     * Afficher une collecte spécifique
     */
    public function show(string $id): JsonResponse
    {
        $collecte = Collecte::with(['site', 'agent', 'hopital', 'factures.features', 'rapports'])
            ->findOrFail($id);

        return response()->json($collecte);
    }

    /**
     * Mettre à jour une collecte
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);

        $validated = $request->validate([
            'date_collecte' => 'date',
            'poids' => 'uuid',
            'nature_dbm' => 'integer',
            'agent_id' => 'uuid|exists:utilisateurs,user_id',
            'hopital_id' => 'uuid|exists:sites,site_id',
            'signature_responsable_site' => 'boolean',
            'isValid' => 'boolean'
        ]);

        $collecte->update($validated);
        $collecte->load(['site', 'agent', 'hopital']);

        return response()->json($collecte);
    }

    /**
     * Supprimer une collecte
     */
    public function destroy(string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);
        $collecte->delete();

        return response()->json(['message' => 'Collecte supprimée avec succès']);
    }

    // ================================
    // GESTION DES RELATIONS COMPLÈTES
    // ================================

    // ============ RELATIONS FACTURES ============

    /**
     * Obtenir toutes les factures d'une collecte
     */
    public function factures(string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);
        $factures = $collecte->factures()->with(['features', 'hopital', 'comptable'])->get();

        return response()->json($factures);
    }

    /**
     * Associer une facture à une collecte
     */
    public function attachFacture(Request $request, string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);

        $validated = $request->validate([
            'facture_id' => 'required|uuid|exists:factures,facture_id'
        ]);

        $collecte->factures()->attach($validated['facture_id']);

        return response()->json(['message' => 'Facture associée à la collecte']);
    }

    /**
     * Associer plusieurs factures à une collecte
     */
    public function attachMultipleFactures(Request $request, string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);

        $validated = $request->validate([
            'facture_ids' => 'required|array',
            'facture_ids.*' => 'uuid|exists:factures,facture_id'
        ]);

        $collecte->factures()->attach($validated['facture_ids']);

        return response()->json(['message' => 'Factures associées à la collecte']);
    }

    /**
     * Détacher une facture d'une collecte
     */
    public function detachFacture(string $collecteId, string $factureId): JsonResponse
    {
        $collecte = Collecte::findOrFail($collecteId);
        $collecte->factures()->detach($factureId);

        return response()->json(['message' => 'Facture détachée de la collecte']);
    }

    /**
     * Détacher toutes les factures d'une collecte
     */
    public function detachAllFactures(string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);
        $collecte->factures()->detach();

        return response()->json(['message' => 'Toutes les factures détachées']);
    }

    /**
     * Synchroniser les factures d'une collecte
     */
    public function syncFactures(Request $request, string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);

        $validated = $request->validate([
            'facture_ids' => 'required|array',
            'facture_ids.*' => 'uuid|exists:factures,facture_id'
        ]);

        $collecte->factures()->sync($validated['facture_ids']);

        return response()->json(['message' => 'Factures synchronisées']);
    }

    // ============ RELATIONS RAPPORTS ============

    /**
     * Obtenir les rapports d'une collecte
     */
    public function rapports(string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);
        $rapports = $collecte->rapports()->with('hopital')->get();

        return response()->json($rapports);
    }

    /**
     * Créer un rapport pour une collecte
     */
    public function createRapport(Request $request, string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);

        $validated = $request->validate([
            'periode' => 'required|string',
            'type' => 'required|string',
            'contenu' => 'required|string',
            'hopital_id' => 'required|uuid|exists:sites,site_id'
        ]);

        $rapport = $collecte->rapports()->create($validated);

        return response()->json($rapport, 201);
    }

    /**
     * Mettre à jour un rapport d'une collecte
     */
    public function updateRapport(Request $request, string $collecteId, string $rapportId): JsonResponse
    {
        $collecte = Collecte::findOrFail($collecteId);
        $rapport = $collecte->rapports()->findOrFail($rapportId);

        $validated = $request->validate([
            'periode' => 'string',
            'type' => 'string',
            'contenu' => 'string',
            'hopital_id' => 'uuid|exists:sites,site_id'
        ]);

        $rapport->update($validated);

        return response()->json($rapport);
    }

    /**
     * Supprimer un rapport d'une collecte
     */
    public function deleteRapport(string $collecteId, string $rapportId): JsonResponse
    {
        $collecte = Collecte::findOrFail($collecteId);
        $rapport = $collecte->rapports()->findOrFail($rapportId);
        $rapport->delete();

        return response()->json(['message' => 'Rapport supprimé']);
    }

    // ============ RELATIONS SITE ============

    /**
     * Obtenir le site d'une collecte avec tous ses détails
     */
    public function site(string $id): JsonResponse
    {
        $collecte = Collecte::with([
            'site' => function ($query) {
                $query->with('responsable', 'utilisateurs');
            }
        ])->findOrFail($id);

        return response()->json($collecte->site);
    }

    /**
     * Changer le site d'une collecte
     */
    public function changeSite(Request $request, string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);

        $validated = $request->validate([
            'site_id' => 'required|uuid|exists:sites,site_id'
        ]);

        // Vérifier que le site appartient au même hôpital
        $site = Site::findOrFail($validated['site_id']);
        if ($site->hopital_id !== $collecte->hopital_id) {
            return response()->json(['error' => 'Le site doit appartenir au même hôpital'], 422);
        }

        $collecte->update(['site_id' => $validated['site_id']]);
        $collecte->load('site');

        return response()->json($collecte);
    }

    // ============ RELATIONS AGENT ============

    /**
     * Obtenir l'agent d'une collecte
     */
    public function agent(string $id): JsonResponse
    {
        $collecte = Collecte::with('agent')->findOrFail($id);

        return response()->json($collecte->agent);
    }

    /**
     * Changer l'agent d'une collecte
     */
    public function changeAgent(Request $request, string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);

        $validated = $request->validate([
            'agent_id' => 'required|uuid|exists:utilisateurs,user_id'
        ]);

        $collecte->update(['agent_id' => $validated['agent_id']]);
        $collecte->load('agent');

        return response()->json($collecte);
    }

    /**
     * Historique des collectes d'un agent
     */
    public function agentHistory(string $agentId): JsonResponse
    {
        $collectes = Collecte::with(['site', 'hopital'])
            ->where('agent_id', $agentId)
            ->orderBy('date_collecte', 'desc')
            ->paginate(20);

        return response()->json($collectes);
    }

    // ============ RELATIONS HÔPITAL ============

    /**
     * Obtenir l'hôpital d'une collecte
     */
    public function hopital(string $id): JsonResponse
    {
        $collecte = Collecte::with([
            'hopital' => function ($query) {
                $query->with('responsable', 'sites');
            }
        ])->findOrFail($id);

        return response()->json($collecte->hopital);
    }

    /**
     * Changer l'hôpital d'une collecte
     */
    public function changeHopital(Request $request, string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);

        $validated = $request->validate([
            'hopital_id' => 'required|uuid|exists:sites,site_id'
        ]);

        $collecte->update(['hopital_id' => $validated['hopital_id']]);
        $collecte->load('hopital');

        return response()->json($collecte);
    }

    /**
     * Obtenir toutes les collectes d'un hôpital avec pagination
     */
    public function hopitalCollectes(string $hopitalId): JsonResponse
    {
        $collectes = Collecte::with(['site', 'agent'])
            ->where('hopital_id', $hopitalId)
            ->orderBy('date_collecte', 'desc')
            ->paginate(20);

        return response()->json($collectes);
    }

    /**
     * Statistiques des collectes par hôpital
     */
    public function hopitalStats(string $hopitalId): JsonResponse
    {
        $stats = [
            'total_collectes' => Collecte::where('hopital_id', $hopitalId)->count(),
            'collectes_validees' => Collecte::where('hopital_id', $hopitalId)->where('isValid', true)->count(),
            'poids_total' => Collecte::where('hopital_id', $hopitalId)->sum('poids'),
            'collectes_ce_mois' => Collecte::where('hopital_id', $hopitalId)
                ->whereMonth('date_collecte', now()->month)->count(),
            'derniere_collecte' => Collecte::where('hopital_id', $hopitalId)
                ->latest('date_collecte')->first()?->date_collecte
        ];

        return response()->json($stats);
    }

    // ============ RELATIONS FEATURES DES FACTURES ============

    /**
     * Obtenir toutes les features des factures d'une collecte
     */
    public function factureFeatures(string $id): JsonResponse
    {
        $collecte = Collecte::with([
            'factures.features' => function ($query) {
                $query->with('hopital', 'comptable');
            }
        ])->findOrFail($id);

        $features = $collecte->factures->pluck('features')->flatten();

        return response()->json($features);
    }

    // ============ RELATIONS UTILISATEURS DU SITE ============

    /**
     * Obtenir tous les utilisateurs du site de la collecte
     */
    public function siteUsers(string $id): JsonResponse
    {
        $collecte = Collecte::with([
            'site.utilisateurs' => function ($query) {
                $query->where('isActive', true);
            }
        ])->findOrFail($id);

        return response()->json($collecte->site->utilisateurs);
    }

    // ============ RELATIONS CROISÉES ============

    /**
     * Obtenir toutes les collectes liées à un responsable de site
     */
    public function byResponsableSite(string $responsableId): JsonResponse
    {
        $collectes = Collecte::whereHas('site', function ($query) use ($responsableId) {
            $query->where('responsable', $responsableId);
        })->with(['site', 'agent', 'hopital'])->orderBy('date_collecte', 'desc')->paginate(20);

        return response()->json($collectes);
    }

    /**
     * Obtenir les collectes d'un département
     */
    public function byDepartement(string $departement): JsonResponse
    {
        $collectes = Collecte::whereHas('site', function ($query) use ($departement) {
            $query->where('site_departement', $departement);
        })->with(['site', 'agent', 'hopital'])->orderBy('date_collecte', 'desc')->get();

        return response()->json($collectes);
    }

    /**
     * Obtenir les collectes d'une commune
     */
    public function byCommune(string $commune): JsonResponse
    {
        $collectes = Collecte::whereHas('site', function ($query) use ($commune) {
            $query->where('site_commune', $commune);
        })->with(['site', 'agent', 'hopital'])->orderBy('date_collecte', 'desc')->get();

        return response()->json($collectes);
    }

    // ================================
    // FONCTIONS DE VALIDATION
    // ================================

    /**
     * Valider une collecte
     */
    public function validate(string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);
        $collecte->update(['isValid' => true]);

        return response()->json(['message' => 'Collecte validée', 'collecte' => $collecte]);
    }

    /**
     * Invalider une collecte
     */
    public function invalidate(string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);
        $collecte->update(['isValid' => false]);

        return response()->json(['message' => 'Collecte invalidée', 'collecte' => $collecte]);
    }

    /**
     * Signer une collecte par le responsable du site
     */
    public function sign(string $id): JsonResponse
    {
        $collecte = Collecte::findOrFail($id);
        $collecte->update(['signature_responsable_site' => true]);

        return response()->json(['message' => 'Collecte signée', 'collecte' => $collecte]);
    }

    // ================================
    // FONCTIONS DE RECHERCHE ET FILTRAGE
    // ================================

    /**
     * Rechercher des collectes par critères
     */
    public function search(Request $request): JsonResponse
    {
        $query = Collecte::with(['site', 'agent', 'hopital']);

        // Filtres par date
        if ($request->date_debut) {
            $query->whereDate('date_collecte', '>=', $request->date_debut);
        }
        if ($request->date_fin) {
            $query->whereDate('date_collecte', '<=', $request->date_fin);
        }

        // Filtre par statut de validation
        if ($request->has('isValid')) {
            $query->where('isValid', $request->boolean('isValid'));
        }

        // Filtre par signature
        if ($request->has('signature')) {
            $query->where('signature_responsable_site', $request->boolean('signature'));
        }

        // Filtre par poids minimum/maximum
        if ($request->poids_min) {
            $query->where('poids', '>=', $request->poids_min);
        }
        if ($request->poids_max) {
            $query->where('poids', '<=', $request->poids_max);
        }

        // Filtre par nature DBM
        if ($request->nature_dbm) {
            $query->where('nature_dbm', $request->nature_dbm);
        }

        // Filtre par localisation (département, commune)
        if ($request->departement) {
            $query->whereHas('site', fn($q) => $q->where('site_departement', $request->departement));
        }
        if ($request->commune) {
            $query->whereHas('site', fn($q) => $q->where('site_commune', $request->commune));
        }

        $collectes = $query->orderBy('date_collecte', 'desc')->paginate(15);

        return response()->json($collectes);
    }

    /**
     * Obtenir les collectes par hôpital
     */
    public function byHopital(string $hopitalId): JsonResponse
    {
        $collectes = Collecte::with(['site', 'agent'])
            ->where('hopital_id', $hopitalId)
            ->orderBy('date_collecte', 'desc')
            ->get();

        return response()->json($collectes);
    }

    /**
     * Obtenir les collectes par agent
     */
    public function byAgent(string $agentId): JsonResponse
    {
        $collectes = Collecte::with(['site', 'hopital'])
            ->where('agent_id', $agentId)
            ->orderBy('date_collecte', 'desc')
            ->get();

        return response()->json($collectes);
    }

    /**
     * Obtenir les collectes par site
     */
    public function bySite(string $siteId): JsonResponse
    {
        $collectes = Collecte::with(['agent', 'hopital'])
            ->whereHas('site', fn($q) => $q->where('site_id', $siteId))
            ->orderBy('date_collecte', 'desc')
            ->get();

        return response()->json($collectes);
    }

    // ================================
    // FONCTIONS DE STATISTIQUES
    // ================================

    /**
     * Obtenir les statistiques générales des collectes
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_collectes' => Collecte::count(),
            'collectes_validees' => Collecte::where('isValid', true)->count(),
            'collectes_signees' => Collecte::where('signature_responsable_site', true)->count(),
            'poids_total' => Collecte::sum('poids'),
            'collectes_ce_mois' => Collecte::whereMonth('date_collecte', now()->month)->count(),
            'collectes_aujourd_hui' => Collecte::whereDate('date_collecte', now()->toDateString())->count()
        ];

        return response()->json($stats);
    }

    /**
     * Obtenir les statistiques par période
     */
    public function statisticsByPeriod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
            'groupby' => 'in:day,week,month'
        ]);

        $groupBy = $validated['groupby'] ?? 'day';

        $query = Collecte::whereBetween('date_collecte', [$validated['date_debut'], $validated['date_fin']]);

        $format = match ($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $stats = $query->selectRaw("
                DATE_FORMAT(date_collecte, '$format') as periode,
                COUNT(*) as nombre_collectes,
                SUM(poids) as poids_total,
                SUM(CASE WHEN isValid = 1 THEN 1 ELSE 0 END) as collectes_validees
            ")
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        return response()->json($stats);
    }

    /**
     * Obtenir les statistiques par hôpital
     */
    public function statisticsByHopital(): JsonResponse
    {
        $stats = Collecte::selectRaw('
                hopital_id,
                COUNT(*) as nombre_collectes,
                SUM(poids) as poids_total,
                AVG(poids) as poids_moyen,
                SUM(CASE WHEN isValid = 1 THEN 1 ELSE 0 END) as collectes_validees
            ')
            ->with('hopital:site_id,site_name')
            ->groupBy('hopital_id')
            ->get();

        return response()->json($stats);
    }

    /**
     * Obtenir les statistiques par agent
     */
    public function statisticsByAgent(): JsonResponse
    {
        $stats = Collecte::selectRaw('
                agent_id,
                COUNT(*) as nombre_collectes,
                SUM(poids) as poids_total,
                SUM(CASE WHEN isValid = 1 THEN 1 ELSE 0 END) as collectes_validees
            ')
            ->with('agent:user_id,firstname,lastname')
            ->groupBy('agent_id')
            ->get();

        return response()->json($stats);
    }

    // ================================
    // FONCTIONS DE RAPPORT
    // ================================

    /**
     * Générer un rapport de collectes
     */
    public function generateReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date',
            'hopital_id' => 'nullable|uuid',
            'agent_id' => 'nullable|uuid',
            'format' => 'in:json,csv,pdf'
        ]);

        $query = Collecte::with(['site', 'agent', 'hopital'])
            ->whereBetween('date_collecte', [$validated['date_debut'], $validated['date_fin']]);

        if ($validated['hopital_id']) {
            $query->where('hopital_id', $validated['hopital_id']);
        }

        if ($validated['agent_id']) {
            $query->where('agent_id', $validated['agent_id']);
        }

        $collectes = $query->get();

        // Ici vous pourriez implémenter la génération selon le format demandé
        return response()->json([
            'rapport' => $collectes,
            'resume' => [
                'total_collectes' => $collectes->count(),
                'poids_total' => $collectes->sum('poids'),
                'collectes_validees' => $collectes->where('isValid', true)->count()
            ]
        ]);
    }

    // ================================
    // FONCTIONS D'EXPORT
    // ================================

    /**
     * Exporter les collectes en CSV
     */
    public function exportCSV(Request $request)
    {
        $collectes = Collecte::with(['site', 'agent', 'hopital'])->get();

        $filename = 'collectes_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        return response()->streamDownload(function () use ($collectes) {
            $handle = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($handle, [
                'ID',
                'Date Collecte',
                'Poids',
                'Nature DBM',
                'Agent',
                'Hôpital',
                'Signature',
                'Validée',
                'Créé le'
            ]);

            // Données
            foreach ($collectes as $collecte) {
                fputcsv($handle, [
                    $collecte->collecte_id,
                    $collecte->date_collecte,
                    $collecte->poids,
                    $collecte->nature_dbm,
                    $collecte->agent->firstname . ' ' . $collecte->agent->lastname,
                    $collecte->hopital->site_name,
                    $collecte->signature_responsable_site ? 'Oui' : 'Non',
                    $collecte->isValid ? 'Oui' : 'Non',
                    $collecte->created_at
                ]);
            }

            fclose($handle);
        }, $filename, $headers);
    }

    // ================================
    // FONCTIONS DE GÉOLOCALISATION
    // ================================

    /**
     * Obtenir les collectes par zone géographique
     */
    public function byLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:1|max:100' // en km
        ]);

        $collectes = Collecte::with(['site', 'agent', 'hopital'])
            ->whereHas('site', function ($query) use ($validated) {
                $lat = $validated['latitude'];
                $lon = $validated['longitude'];
                $radius = $validated['radius'];

                $query->selectRaw("
                                    *, ( 6371 * acos( cos( radians($lat) ) 
                                    * cos( radians( latitude ) ) 
                                    * cos( radians( longitude ) - radians($lon) ) 
                                    + sin( radians($lat) ) 
                                    * sin( radians( latitude ) ) ) ) AS distance
                                ")->having('distance', '<', $radius);
            })
            ->get();

        return response()->json($collectes);
    }

    // ================================
    // FONCTIONS DE NOTIFICATIONS
    // ================================

    /**
     * Obtenir les collectes nécessitant une attention
     */
    public function pending(): JsonResponse
    {
        $collectes = Collecte::with(['site', 'agent', 'hopital'])
            ->where(function ($query) {
                $query->where('isValid', false)
                    ->orWhere('signature_responsable_site', false)
                    ->orWhere('date_collecte', '<', now()->subDays(7));
            })
            ->orderBy('date_collecte', 'asc')
            ->get();

        return response()->json($collectes);
    }

    /**
     * Obtenir les collectes récentes (dernières 24h)
     */
    public function recent(): JsonResponse
    {
        $collectes = Collecte::with(['site', 'agent', 'hopital'])
            ->where('date_collecte', '>=', now()->subDay())
            ->orderBy('date_collecte', 'desc')
            ->get();

        return response()->json($collectes);
    }
}
