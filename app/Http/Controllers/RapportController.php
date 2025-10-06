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

class RapportController extends Controller
{

    /**
     * Rapport des collectes
     */
    public function collectes(Request $request)
    {
        // Filtres
        $dateDebut = $request->input('date_debut', now()->startOfMonth());
        $dateFin = $request->input('date_fin', now()->endOfMonth());
        $siteId = $request->input('site_id');
        $agentId = $request->input('agent_id');
        $typeDechetId = $request->input('type_dechet_id');
        $statut = $request->input('statut');

        // Query de base
        $collectesQuery = Collecte::with(['site', 'agent', 'typeDechet'])
            ->whereBetween('date_collecte', [$dateDebut, $dateFin]);

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
            ->whereBetween('date_collecte', [$dateDebut, $dateFin])
            ->groupBy('users.user_id', 'users.firstname', 'users.lastname')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Données pour les filtres
        $sites = Site::select('site_id', 'site_name')->get();
        $agents = User::select('user_id', 'firstname', 'lastname')->get(); // Retiré ->role() qui peut causer des problèmes
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
            'dateDebut',
            'dateFin',
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
        $departements = Site::select('site_departement')->distinct()->pluck('site_departement');
        $communes = Site::select('site_commune')->distinct()->pluck('site_commune');

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
        // À implémenter avec Laravel Excel
        // return Excel::download(new CollectesExport($request), 'collectes.xlsx');
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
}
