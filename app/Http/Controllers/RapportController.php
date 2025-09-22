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
     * Rapport financier
     */
    public function financier(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth());
        $dateFin = $request->input('date_fin', now()->endOfMonth());
        $siteId = $request->input('site_id');

        // Statistiques des factures
        $facturesQuery = Facture::whereBetween('date_facture', [$dateDebut, $dateFin]);
        if ($siteId) {
            $facturesQuery->where('site_id', $siteId);
        }

        $statsFactures = [
            'total_factures' => $facturesQuery->count(),
            'montant_total_facture' => $facturesQuery->sum('montant_facture'),
            'factures_payees' => (clone $facturesQuery)->where('statut', 'payee')->count(),
            'factures_impayees' => (clone $facturesQuery)->where('statut', 'impayee')->count(),
        ];

        // Statistiques des paiements
        $paiementsQuery = Paiement::whereBetween('date_paiement', [$dateDebut, $dateFin]);
        if ($siteId) {
            $paiementsQuery->whereHas('facture', function ($q) use ($siteId) {
                $q->where('site_id', $siteId);
            });
        }

        $statsPaiements = [
            'total_paiements' => $paiementsQuery->count(),
            'montant_total_paye' => $paiementsQuery->sum('montant'),
            'paiements_valides' => (clone $paiementsQuery)->where('statut', 'valide')->count(),
            'paiements_en_attente' => (clone $paiementsQuery)->where('statut', 'en attente')->count(),
        ];

        // Taux de recouvrement
        $tauxRecouvrement = $statsFactures['montant_total_facture'] > 0
            ? ($statsPaiements['montant_total_paye'] / $statsFactures['montant_total_facture']) * 100
            : 0;

        // Évolution du CA mensuel
        $evolutionCA = Facture::select(
            DB::raw('DATE_FORMAT(date_facture, "%Y-%m") as mois'),
            DB::raw('sum(montant_facture) as ca')
        )
            ->where('date_facture', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(date_facture, "%Y-%m")'))
            ->orderBy('mois')
            ->get();

        // CA par site
        $caBySite = Facture::select('sites.site_name', DB::raw('sum(montant_facture) as ca'))
            ->join('sites', 'factures.site_id', '=', 'sites.site_id')
            ->whereBetween('date_facture', [$dateDebut, $dateFin])
            ->groupBy('sites.site_id', 'sites.site_name')
            ->orderBy('ca', 'desc')
            ->get();

        // Répartition par mode de paiement
        $repartitionModePaiement = Paiement::select('mode_paiement', DB::raw('count(*) as total'))
            ->whereBetween('date_paiement', [$dateDebut, $dateFin])
            ->groupBy('mode_paiement')
            ->pluck('total', 'mode_paiement');

        // Factures récentes
        $facturesRecentes = Facture::with(['site', 'comptable'])
            ->whereBetween('date_facture', [$dateDebut, $dateFin])
            ->orderBy('date_facture', 'desc')
            ->limit(10)
            ->get();

        $sites = Site::select('site_id', 'site_name')->get();

        return view('rapports.financier', compact(
            'statsFactures',
            'statsPaiements',
            'tauxRecouvrement',
            'evolutionCA',
            'caBySite',
            'repartitionModePaiement',
            'facturesRecentes',
            'sites',
            'dateDebut',
            'dateFin',
            'siteId'
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
}
