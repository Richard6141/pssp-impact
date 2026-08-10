<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collecte;
use App\Models\Facture;
use App\Models\Paiement;
use App\Models\Site;
use App\Models\User;
use App\Models\TypeDechet;
use App\Models\Incident;
use App\Models\Observation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF; // ou use Barryvdh\DomPDF\Facade\Pdf;
use Excel; // ou use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FinancierExport;
use App\Traits\HasVisibleSiteIds;

class RapportController extends Controller
{
    use HasVisibleSiteIds;
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


    /**
     * Rapport des collectes
     */
    public function collectes(Request $request)
    {
        // Filtres
        $minDate = Collecte::min('date_collecte');
        $maxDate = Collecte::max('date_collecte');
        $defaultStart = $minDate
            ? Carbon::parse($maxDate)->subMonths(6)->startOfDay()
            : now()->subMonths(6)->startOfDay();
        $defaultEnd = $maxDate
            ? Carbon::parse($maxDate)->endOfDay()
            : now()->endOfDay();

        $dateDebutInput = $request->input('date_debut', $defaultStart->format('Y-m-d'));
        $dateFinInput = $request->input('date_fin', $defaultEnd->format('Y-m-d'));

        $dateDebut = Carbon::parse($dateDebutInput)->startOfDay();
        $dateFin = Carbon::parse($dateFinInput)->endOfDay();
        $siteId = $request->input('site_id');
        $agentId = $request->input('agent_id');
        $typeDechetId = $request->input('type_dechet_id');
        $statut = $request->input('statut');

        $user = auth()->user();
        if ($user->hasRole('Agent collecte')) {
            $agentId = $user->user_id;
        }

        // Query de base
        $collectesQuery = Collecte::with(['site', 'agent', 'typeDechet'])
            ->whereBetween('date_collecte', [$dateDebut, $dateFin]);

        $this->applyCollecteVisibility($collectesQuery);

        // Application des filtres
        if ($siteId) {
            $collectesQuery->where('site_id', $siteId);
        }
        if ($agentId) {
            $collectesQuery->where('agent_id', $agentId);
        }
        if ($typeDechetId) {
            $collectesQuery->where('type_dechet_id', $typeDechetId);
        }
        if ($statut) {
            $collectesQuery->where('statut', $statut);
        }

        $collectes = $collectesQuery->orderBy('date_collecte', 'desc')->paginate(50);

        // Statistiques générales - Créer une nouvelle query pour éviter les conflits
        $statsQuery = Collecte::whereBetween('date_collecte', [$dateDebut, $dateFin]);
        $this->applyCollecteVisibility($statsQuery);
        if ($siteId) {
            $statsQuery->where('site_id', $siteId);
        }
        if ($agentId) {
            $statsQuery->where('agent_id', $agentId);
        }
        if ($typeDechetId) {
            $statsQuery->where('type_dechet_id', $typeDechetId);
        }
        if ($statut) {
            $statsQuery->where('statut', $statut);
        }

        $stats = [
            'total_collectes' => $statsQuery->count(),
            'poids_total' => $statsQuery->sum('poids'),
            'collectes_validees' => $statsQuery->where('isValid', true)->count(),
            'collectes_signees' => $statsQuery->where('signature_responsable_site', true)->count(),
        ];

        // Répartition par statut - CORRECTION: Nouvelle query indépendante
        $repartitionStatutQuery = Collecte::whereBetween('date_collecte', [$dateDebut, $dateFin]);
        $this->applyCollecteVisibility($repartitionStatutQuery);
        if ($siteId) {
            $repartitionStatutQuery->where('site_id', $siteId);
        }
        if ($agentId) {
            $repartitionStatutQuery->where('agent_id', $agentId);
        }
        if ($typeDechetId) {
            $repartitionStatutQuery->where('type_dechet_id', $typeDechetId);
        }
        // Ne pas appliquer le filtre statut pour la répartition

        $repartitionStatut = $repartitionStatutQuery->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut');

        // Répartition par type de déchet - CORRECTION: Nouvelle query indépendante
        $repartitionTypeDechetQuery = Collecte::whereBetween('date_collecte', [$dateDebut, $dateFin]);
        $this->applyCollecteVisibility($repartitionTypeDechetQuery);
        if ($siteId) {
            $repartitionTypeDechetQuery->where('site_id', $siteId);
        }
        if ($agentId) {
            $repartitionTypeDechetQuery->where('agent_id', $agentId);
        }
        if ($statut) {
            $repartitionTypeDechetQuery->where('statut', $statut);
        }
        // Ne pas appliquer le filtre type_dechet pour la répartition

        $repartitionTypeDechet = $repartitionTypeDechetQuery->select('type_dechets.libelle', DB::raw('count(*) as total'))
            ->join('type_dechets', 'collectes.type_dechet_id', '=', 'type_dechets.type_dechet_id')
            ->groupBy('type_dechets.libelle')
            ->pluck('total', 'libelle');

        // Évolution mensuelle (6 derniers mois)
        $evolutionMensuelle = Collecte::select(
            DB::raw('DATE_FORMAT(date_collecte, "%Y-%m") as mois'),
            DB::raw('count(*) as total'),
            DB::raw('sum(poids) as poids_total')
        )
            ->where('date_collecte', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('DATE_FORMAT(date_collecte, "%Y-%m")'))
            ->orderBy('mois')
            ->get();

        // Top agents par nombre de collectes
        $topAgents = Collecte::select('users.firstname', 'users.lastname', DB::raw('count(*) as total'))
            ->join('users', 'collectes.agent_id', '=', 'users.user_id')
            ->when($user->hasRole('Responsable site'), function ($q) use ($user) {
                $q->join('sites', 'collectes.site_id', '=', 'sites.site_id')
                    ->where('sites.responsable', $user->user_id);
            })
            ->when($user->hasRole('Agent collecte'), function ($q) use ($user) {
                $q->where('collectes.agent_id', $user->user_id);
            })
            ->whereBetween('date_collecte', [$dateDebut, $dateFin])
            ->groupBy('users.user_id', 'users.firstname', 'users.lastname')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Données pour les filtres
        $sitesQuery = Site::select('site_id', 'site_name');
        $this->applySiteVisibility($sitesQuery);
        $sites = $sitesQuery->get();
        if ($user->hasRole('Agent collecte')) {
            $agents = User::select('user_id', 'firstname', 'lastname')
                ->where('user_id', $user->user_id)
                ->get();
        } else {
            $agents = User::select('user_id', 'firstname', 'lastname')->get(); // Retir? ->role() qui peut causer des probl?mes
        }
        $typesDechets = TypeDechet::select('type_dechet_id', 'libelle')->get();

        return view('rapports.rapports', compact(
            'collectes',
            'stats',
            'repartitionStatut',
            'repartitionTypeDechet',
            'evolutionMensuelle',
            'topAgents',
            'sites',
            'agents',
            'typesDechets',
            'dateDebutInput',
            'dateFinInput',
            'siteId',
            'agentId',
            'typeDechetId',
            'statut'
        ));
    }



    /**
     * Rapport par sites
     */
    public function sites(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth());
        $dateFin = $request->input('date_fin', now()->endOfMonth());
        $departement = $request->input('departement');
        $commune = $request->input('commune');

        // Query des sites avec statistiques
        $sitesQuery = Site::with(['responsable'])
            ->select('sites.*')
            ->addSelect([
                'total_collectes' => Collecte::selectRaw('count(*)')
                    ->whereColumn('collectes.site_id', 'sites.site_id')
                    ->whereBetween('date_collecte', [$dateDebut, $dateFin]),

                'poids_total' => Collecte::selectRaw('COALESCE(sum(poids), 0)')
                    ->whereColumn('collectes.site_id', 'sites.site_id')
                    ->whereBetween('date_collecte', [$dateDebut, $dateFin]),

                'ca_total' => Facture::selectRaw('COALESCE(sum(montant_facture), 0)')
                    ->whereColumn('factures.site_id', 'sites.site_id')
                    ->whereBetween('date_facture', [$dateDebut, $dateFin]),

                'total_observations' => Observation::selectRaw('count(*)')
                    ->whereColumn('observations.site_id', 'sites.site_id')
                    ->whereBetween('date_obs', [$dateDebut, $dateFin]),
            ]);

        $this->applySiteVisibility($sitesQuery);

        // Filtres géographiques
        if ($departement) {
            $sitesQuery->where('site_departement', $departement);
        }
        if ($commune) {
            $sitesQuery->where('site_commune', $commune);
        }

        $sites = $sitesQuery->orderBy('total_collectes', 'desc')->paginate(20);

        // Statistiques générales
        $statsGenerales = [
            'total_sites' => Site::count(),
            'sites_actifs' => Site::whereHas('collectes', function ($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('date_collecte', [$dateDebut, $dateFin]);
            })->count(),
            'moyenne_collectes_par_site' => $sites->avg('total_collectes'),
            'moyenne_poids_par_site' => $sites->avg('poids_total'),
        ];

        // Répartition par département
        $repartitionDepartement = Site::select('site_departement', DB::raw('count(*) as total'))
            ->groupBy('site_departement')
            ->pluck('total', 'site_departement');

        // Top 10 sites les plus actifs
        $topSites = Site::select('sites.*')
            ->addSelect([
                'total_collectes' => Collecte::selectRaw('count(*)')
                    ->whereColumn('collectes.site_id', 'sites.site_id')
                    ->whereBetween('date_collecte', [$dateDebut, $dateFin])
            ])
            ->orderBy('total_collectes', 'desc')
            ->limit(10)
            ->get();

        // Sites avec incidents
        $sitesAvecIncidents = Site::select('sites.*')
            ->addSelect([
                'total_incidents' => Incident::selectRaw('count(*)')
                    ->join('collectes', 'incidents.collecte_id', '=', 'collectes.collecte_id')
                    ->whereColumn('collectes.site_id', 'sites.site_id')
                    ->whereBetween('incidents.date_incident', [$dateDebut, $dateFin])
            ])
            ->havingRaw('total_incidents > 0')
            ->orderBy('total_incidents', 'desc')
            ->get();

        // Données pour filtres
        $departementsQuery = Site::select('site_departement')->distinct();
        $communesQuery = Site::select('site_commune')->distinct();
        $this->applySiteVisibility($departementsQuery);
        $this->applySiteVisibility($communesQuery);
        $departements = $departementsQuery->pluck('site_departement');
        $communes = $communesQuery->pluck('site_commune');

        return view('rapports.sites', compact(
            'sites',
            'statsGenerales',
            'repartitionDepartement',
            'topSites',
            'sitesAvecIncidents',
            'departements',
            'communes',
            'dateDebut',
            'dateFin',
            'departement',
            'commune'
        ));
    }

    /**
     * Export PDF d'un rapport
     */
    public function exportPdf(Request $request, $type)
    {
        // Logique d'export selon le type
        switch ($type) {
            case 'collectes':
                return $this->exportCollectesPdf($request);
            case 'financier':
                return $this->exportFinancierPdf($request);
            case 'sites':
                return $this->exportSitesPdf($request);
            default:
                abort(404);
        }
    }

    /**
     * Export Excel d'un rapport
     */
    public function exportExcel(Request $request, $type)
    {
        // � impl�menter avec Laravel Excel
        // return Excel::download(new CollectesExport($request), 'collectes.xlsx');
    }

    public function exportTxt(Request $request, $type)
    {
        switch ($type) {
            case 'collectes':
                return $this->exportCollectesTxt($request);
            default:
                abort(404);
        }
    }

    public function financier(Request $request)
    {
        $now = Carbon::now();

        // Récupération des filtres
        $dateDebut = $request->get('date_debut', $now->copy()->subMonths(6)->format('Y-m-d'));
        $dateFin = $request->get('date_fin', $now->format('Y-m-d'));
        $siteId = $request->get('site_id');
        $statutFacture = $request->get('statut_facture');
        $modePaiement = $request->get('mode_paiement');

        // Query de base pour les factures
        $facturesQuery = Facture::with(['site', 'paiements'])
            ->whereBetween('date_facture', [$dateDebut, $dateFin]);

        if ($siteId) {
            $facturesQuery->where('site_id', $siteId);
        }

        if ($statutFacture) {
            $facturesQuery->where('statut', $statutFacture);
        }

        // Query pour les paiements
        $paiementsQuery = Paiement::with(['facture.site'])
            ->whereBetween('date_paiement', [$dateDebut, $dateFin]);

        if ($modePaiement) {
            $paiementsQuery->where('mode_paiement', $modePaiement);
        }

        // ===== STATISTIQUES PRINCIPALES =====

        $stats = [
            'revenu_total' => $paiementsQuery->clone()->sum('montant'),
            'total_factures' => $facturesQuery->clone()->count(),
            'montant_facture' => $facturesQuery->clone()->sum('montant_facture'),
            'total_paiements' => $paiementsQuery->clone()->count(),
            'montant_paiements' => $paiementsQuery->clone()->sum('montant'),
            'factures_impayees' => $facturesQuery->clone()->where('statut', '!=', 'payee')->count(),
            'montant_impayes' => $facturesQuery->clone()->where('statut', '!=', 'payee')->sum('montant_facture')
        ];

        // ===== ÉVOLUTION MENSUELLE (6 derniers mois) =====

        $evolutionMensuelle = collect();
        for ($i = 5; $i >= 0; $i--) {
            $mois = $now->copy()->subMonths($i);

            $montantFactures = Facture::whereMonth('date_facture', $mois->month)
                ->whereYear('date_facture', $mois->year)
                ->when($siteId, fn($q) => $q->where('site_id', $siteId))
                ->sum('montant_facture');

            $montantPaiements = Paiement::whereMonth('date_paiement', $mois->month)
                ->whereYear('date_paiement', $mois->year)
                ->when($siteId, function ($q) use ($siteId) {
                    $q->whereHas('facture', fn($query) => $query->where('site_id', $siteId));
                })
                ->sum('montant');

            $evolutionMensuelle->push([
                'mois' => $mois->format('M Y'),
                'montant_factures' => $montantFactures,
                'montant_paiements' => $montantPaiements
            ]);
        }

        // ===== RÉPARTITION PAR MODE DE PAIEMENT =====

        $repartitionModePaiement = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->when($siteId, function ($q) use ($siteId) {
                $q->whereHas('facture', fn($query) => $query->where('site_id', $siteId));
            })
            ->selectRaw('mode_paiement, SUM(montant) as total')
            ->groupBy('mode_paiement')
            ->pluck('total', 'mode_paiement')
            ->mapWithKeys(function ($value, $key) {
                return [ucfirst(str_replace('_', ' ', $key)) => $value];
            });

        // ===== RÉPARTITION PAR STATUT FACTURE =====

        $repartitionStatutFacture = Facture::whereBetween('date_facture', [$dateDebut, $dateFin])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->mapWithKeys(function ($value, $key) {
                return [ucfirst(str_replace('_', ' ', $key)) => $value];
            });

        // ===== TOP SITES PAR REVENUS =====

        $topSites = DB::table('factures')
            ->join('sites', 'factures.site_id', '=', 'sites.site_id')
            ->select(
                'sites.site_name',
                DB::raw('COUNT(factures.facture_id) as nombre_factures'),
                DB::raw('SUM(factures.montant_facture) as montant_total')
            )
            ->whereBetween('factures.date_facture', [$dateDebut, $dateFin])
            ->when($siteId, fn($q) => $q->where('sites.site_id', $siteId))
            ->groupBy('sites.site_id', 'sites.site_name')
            ->orderBy('montant_total', 'desc')
            ->limit(5)
            ->get();

        // ===== DERNIERS PAIEMENTS =====

        $derniersPaiements = Paiement::with('facture')
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->when($siteId, function ($q) use ($siteId) {
                $q->whereHas('facture', fn($query) => $query->where('site_id', $siteId));
            })
            ->when($modePaiement, fn($q) => $q->where('mode_paiement', $modePaiement))
            ->orderBy('date_paiement', 'desc')
            ->limit(10)
            ->get();

        // ===== LISTE DES FACTURES AVEC PAGINATION =====

        $factures = $facturesQuery->orderBy('date_facture', 'desc')->paginate(15);

        // ===== DONNÉES POUR LES FILTRES =====

        $sites = Site::orderBy('site_name')->get();

        // ===== GESTION DES EXPORTS =====

        if ($request->has('export')) {
            if ($request->export === 'pdf') {
                return $this->exportFinancierPDF($request, compact(
                    'stats',
                    'evolutionMensuelle',
                    'repartitionModePaiement',
                    'repartitionStatutFacture',
                    'topSites',
                    'factures',
                    'dateDebut',
                    'dateFin'
                ));
            } elseif ($request->export === 'excel') {
                return $this->exportFinancierExcel($request, compact(
                    'stats',
                    'evolutionMensuelle',
                    'factures',
                    'dateDebut',
                    'dateFin'
                ));
            }
        }

        return view('rapports.financier', compact(
            'stats',
            'evolutionMensuelle',
            'repartitionModePaiement',
            'repartitionStatutFacture',
            'topSites',
            'derniersPaiements',
            'factures',
            'sites',
            'dateDebut',
            'dateFin',
            'siteId',
            'statutFacture',
            'modePaiement'
        ));
    }

    /**
     * Export du rapport financier en PDF
     */
    private function exportFinancierPDF($request, $data)
    {
        $pdf = PDF::loadView('rapports.financier_pdf', $data);
        return $pdf->download('rapport_financier_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export du rapport financier en Excel
     */
    private function exportFinancierExcel($request, $data)
    {
        return Excel::download(
            new FinancierExport($data),
            'rapport_financier_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export du rapport des collectes en PDF
     */
    private function exportCollectesPdf(Request $request)
    {
        // Filtres
        $dateDebut = Carbon::parse($request->input('date_debut', now()->subMonths(6)->format('Y-m-d')))->startOfDay();
        $dateFin = Carbon::parse($request->input('date_fin', now()->format('Y-m-d')))->endOfDay();
        
        $siteId = $request->input('site_id');
        $agentId = $request->input('agent_id');
        $typeDechetId = $request->input('type_dechet_id');
        $statut = $request->input('statut');

        $user = auth()->user();
        if ($user->hasRole('Agent collecte')) {
            $agentId = $user->user_id;
        }

        $query = Collecte::with(['site', 'agent', 'typeDechet'])
            ->whereBetween('date_collecte', [$dateDebut, $dateFin]);

        $this->applyCollecteVisibility($query);

        if ($siteId) $query->where('site_id', $siteId);
        if ($agentId) $query->where('agent_id', $agentId);
        if ($typeDechetId) $query->where('type_dechet_id', $typeDechetId);
        if ($statut) $query->where('statut', $statut);

        $collectes = $query->orderBy('date_collecte', 'desc')->get();

        // Stats
        $stats = [
            'total_collectes' => $collectes->count(),
            'poids_total' => $collectes->sum('poids'),
            'collectes_validees' => $collectes->where('isValid', true)->count(),
            'collectes_signees' => $collectes->where('signature_responsable_site', true)->count(),
        ];
        
        // Répartition Type Déchet
        $repartitionTypeDechet = $collectes->groupBy(fn($c) => $c->typeDechet->libelle ?? 'Autre')
            ->map(fn($group) => $group->count());

        // Top Agents
        $topAgents = $collectes->groupBy('agent_id')
            ->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'firstname' => $first->agent->firstname ?? '',
                    'lastname' => $first->agent->lastname ?? '',
                    'total' => $group->count()
                ];
            })
            ->sortByDesc('total')
            ->take(10);

        $pdf = PDF::loadView('rapports.collectes_pdf', compact('collectes', 'stats', 'repartitionTypeDechet', 'topAgents', 'dateDebut', 'dateFin'));
        return $pdf->download('rapport_collectes_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export du rapport des sites en PDF
     */
    private function exportSitesPdf(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth());
        $dateFin = $request->input('date_fin', now()->endOfMonth());
        
        $sites = Site::withCount(['collectes' => function($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('date_collecte', [$dateDebut, $dateFin]);
            }])
            ->withSum(['collectes' => function($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('date_collecte', [$dateDebut, $dateFin]);
            }], 'poids')
            ->get();

        $statsGenerales = [
            'total_sites' => $sites->count(),
            'sites_actifs' => $sites->where('collectes_count', '>', 0)->count(),
            'moyenne_collectes_par_site' => $sites->avg('collectes_count'),
            'moyenne_poids_par_site' => $sites->avg('collectes_sum_poids')
        ];

        $repartitionDepartement = $sites->groupBy('site_departement')->map->count();
        
        $topSites = $sites->sortByDesc('collectes_count')->take(10);
        
        // Incidents (simplifié pour l'export)
        $sitesAvecIncidents = Site::whereHas('collectes.incidents', function($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date_incident', [$dateDebut, $dateFin]);
        })->withCount(['collectes as total_incidents' => function($q) use ($dateDebut, $dateFin) {
            $q->whereHas('incidents', function($qi) use ($dateDebut, $dateFin) {
                $qi->whereBetween('date_incident', [$dateDebut, $dateFin]);
            });
        }])->get();

        $pdf = PDF::loadView('rapports.sites_pdf', compact('sites', 'statsGenerales', 'repartitionDepartement', 'topSites', 'sitesAvecIncidents', 'dateDebut', 'dateFin'));
        return $pdf->download('rapport_sites_' . now()->format('Y-m-d') . '.pdf');
    }
    private function exportCollectesTxt(Request $request)
    {
        $dateDebut = Carbon::parse($request->input('date_debut', now()->subMonths(6)->format('Y-m-d')))->startOfDay();
        $dateFin = Carbon::parse($request->input('date_fin', now()->format('Y-m-d')))->endOfDay();

        $siteId = $request->input('site_id');
        $agentId = $request->input('agent_id');
        $typeDechetId = $request->input('type_dechet_id');
        $statut = $request->input('statut');

        $user = auth()->user();
        if ($user->hasRole('Agent collecte')) {
            $agentId = $user->user_id;
        }

        $query = Collecte::with(['site', 'agent', 'typeDechet'])
            ->whereBetween('date_collecte', [$dateDebut, $dateFin]);

        $this->applyCollecteVisibility($query);

        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        if ($agentId) {
            $query->where('agent_id', $agentId);
        }
        if ($typeDechetId) {
            $query->where('type_dechet_id', $typeDechetId);
        }
        if ($statut) {
            $query->where('statut', $statut);
        }

        $collectes = $query->orderBy('date_collecte', 'desc')->get();

        $lines = [];
        $lines[] = 'Periode: ' . $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y');

        foreach ($collectes as $collecte) {
            $date = optional($collecte->date_collecte)->format('d/m/Y') ?: '';
            $reference = $collecte->numero_collecte ?? ($collecte->collecte_id ?? '');
            $codeSite = $collecte->site->site_code ?? ($collecte->site->site_name ?? '');
            $codeTypeDechet = $collecte->typeDechet->code ?? ($collecte->typeDechet->code_dbm ?? '');
            // 3 decimales : la colonne passe de 8 a 12 caracteres pour ne plus
            // tronquer le poids exact enregistre (retour terrain du 10/08/2026).
            $poids = number_format((float) ($collecte->poids ?? 0), Collecte::POIDS_DECIMALES, ',', '');
            $lines[] = sprintf(
                '%-10s| %-10s| %-10s | %-16s| %-12s|',
                mb_substr($date, 0, 10),
                mb_substr($reference, 0, 10),
                mb_substr($codeSite, 0, 10),
                mb_substr($codeTypeDechet, 0, 16),
                mb_substr($poids, 0, 12)
            );
        }

        $content = implode(PHP_EOL, $lines) . PHP_EOL;
        $filename = 'rapport_collectes_' . now()->format('Y-m-d') . '.txt';

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

