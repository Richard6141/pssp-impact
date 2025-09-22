<?php

namespace App\Http\Controllers\backend;

use App\Models\Site;
use App\Models\Facture;
use App\Models\Collecte;
use App\Models\TypeDechet;
use App\Models\Paiement;
use App\Models\Incident;
use App\Models\Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // ===== STATISTIQUES PRINCIPALES =====

        // Collectes avec comparaison mensuelle
        $collectesTotal = Collecte::whereMonth('date_collecte', $now->month)
            ->whereYear('date_collecte', $now->year)
            ->count();

        $collectesMoisPrecedent = Collecte::whereMonth('date_collecte', $now->subMonth()->month)
            ->whereYear('date_collecte', $now->subMonth()->year)
            ->count();

        $croissanceCollectes = $collectesMoisPrecedent > 0
            ? round((($collectesTotal - $collectesMoisPrecedent) / $collectesMoisPrecedent) * 100, 1)
            : 0;

        // Factures avec évolution
        $facturesTotal = Facture::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $facturesMoisPrecedent = Facture::whereMonth('created_at', $now->subMonth()->month)
            ->whereYear('created_at', $now->subMonth()->year)
            ->count();

        $croissanceFactures = $facturesMoisPrecedent > 0
            ? round((($facturesTotal - $facturesMoisPrecedent) / $facturesMoisPrecedent) * 100, 1)
            : 0;

        // Revenus avec comparaison
        $montantTotal = Facture::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('montant_facture');

        $montantMoisPrecedent = Facture::whereMonth('created_at', $now->subMonth()->month)
            ->whereYear('created_at', $now->subMonth()->year)
            ->sum('montant_facture');

        $croissanceRevenus = $montantMoisPrecedent > 0
            ? round((($montantTotal - $montantMoisPrecedent) / $montantMoisPrecedent) * 100, 1)
            : 0;

        // Sites actifs et nouveaux
        $sitesActifs = Site::has('collectes')->count();
        $nouveauxSites = Site::whereMonth('created_at', $now->month)->count();

        // ===== DONNÉES POUR GRAPHIQUES =====

        // Évolution des collectes (7 derniers jours)
        $evolutionCollectes = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $evolutionCollectes[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('D'),
                'collectes' => Collecte::whereDate('date_collecte', $date)->count(),
                'poids' => Collecte::whereDate('date_collecte', $date)->sum('poids')
            ];
        }

        // Répartition par type de déchets avec noms
        $typesDechets = DB::table('collectes')
            ->join('type_dechets', 'collectes.type_dechet_id', '=', 'type_dechets.type_dechet_id')
            ->select(
                'type_dechets.libelle',
                DB::raw('COUNT(*) as nombre'),
                DB::raw('SUM(poids) as poids_total')
            )
            ->whereMonth('collectes.date_collecte', $now->month)
            ->whereYear('collectes.date_collecte', $now->year)
            ->groupBy('type_dechets.type_dechet_id', 'type_dechets.libelle')
            ->get();

        // Évolution mensuelle (12 derniers mois)
        $evolutionMensuelle = [];
        for ($i = 11; $i >= 0; $i--) {
            $mois = Carbon::now()->subMonths($i);
            $evolutionMensuelle[] = [
                'mois' => $mois->format('M Y'),
                'collectes' => Collecte::whereMonth('date_collecte', $mois->month)
                    ->whereYear('date_collecte', $mois->year)
                    ->count(),
                'revenus' => Facture::whereMonth('created_at', $mois->month)
                    ->whereYear('created_at', $mois->year)
                    ->sum('montant_facture')
            ];
        }

        // ===== ACTIVITÉS RÉCENTES DYNAMIQUES =====

        $activitesRecentes = collect();

        // Dernières collectes
        $dernieresCollectes = Collecte::with(['site', 'typeDechet'])
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($collecte) {
                return [
                    'type' => 'collecte',
                    'icone' => 'bi-truck',
                    'couleur' => 'primary',
                    'titre' => 'Nouvelle collecte',
                    'description' => $collecte->site->site_name . ' - ' . $collecte->poids . ' kg',
                    'date' => $collecte->created_at
                ];
            });

        // Dernières factures
        $dernieresFactures = Facture::with('site')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($facture) {
                return [
                    'type' => 'facture',
                    'icone' => 'bi-receipt',
                    'couleur' => 'success',
                    'titre' => 'Facture générée',
                    'description' => $facture->numero_facture . ' - ' . number_format($facture->montant_facture, 0, ',', ' ') . ' FCFA',
                    'date' => $facture->created_at
                ];
            });

        // Derniers paiements
        $derniersPaiements = Paiement::with('facture')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get()
            ->map(function ($paiement) {
                return [
                    'type' => 'paiement',
                    'icone' => 'bi-credit-card',
                    'couleur' => 'info',
                    'titre' => 'Paiement reçu',
                    'description' => number_format($paiement->montant, 0, ',', ' ') . ' FCFA - ' . $paiement->mode_paiement,
                    'date' => $paiement->created_at
                ];
            });

        // Derniers incidents
        $derniersIncidents = Incident::with(['collecte', 'reportedBy'])
            ->orderBy('created_at', 'desc')
            ->take(1)
            ->get()
            ->map(function ($incident) {
                return [
                    'type' => 'incident',
                    'icone' => 'bi-exclamation-triangle',
                    'couleur' => 'danger',
                    'titre' => 'Incident signalé',
                    'description' => substr($incident->description, 0, 50) . '...',
                    'date' => $incident->created_at
                ];
            });

        $activitesRecentes = $activitesRecentes
            ->merge($dernieresCollectes)
            ->merge($dernieresFactures)
            ->merge($derniersPaiements)
            ->merge($derniersIncidents)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        // ===== COLLECTES RÉCENTES AVEC PLUS D'INFOS =====

        $collectesRecentes = Collecte::with(['site', 'typeDechet', 'agent'])
            ->orderBy('date_collecte', 'desc')
            ->take(8)
            ->get()
            ->map(function ($collecte) {
                return [
                    'numero_collecte' => $collecte->numero_collecte,
                    'site_name' => $collecte->site->site_name,
                    'type_dechet' => $collecte->typeDechet->libelle,
                    'poids' => $collecte->poids,
                    'statut' => $collecte->statut,
                    'date_collecte' => $collecte->date_collecte->format('d/m/Y'),
                    'agent' => $collecte->agent->name ?? 'N/A'
                ];
            });

        // ===== INDICATEURS SUPPLÉMENTAIRES =====

        // Taux de validation
        $collectesValidees = Collecte::where('isValid', true)
            ->whereMonth('date_collecte', $now->month)
            ->count();
        $tauxValidation = $collectesTotal > 0 ? round(($collectesValidees / $collectesTotal) * 100, 1) : 0;

        // Factures impayées
        $facturesImpayees = Facture::where('statut', '!=', 'payee')
            ->whereMonth('created_at', $now->month)
            ->count();

        // Top 5 des sites les plus actifs
        $topSites = DB::table('collectes')
            ->join('sites', 'collectes.site_id', '=', 'sites.site_id')
            ->select(
                'sites.site_name',
                DB::raw('COUNT(*) as nombre_collectes'),
                DB::raw('SUM(poids) as poids_total')
            )
            ->whereMonth('collectes.date_collecte', $now->month)
            ->groupBy('sites.site_id', 'sites.site_name')
            ->orderBy('nombre_collectes', 'desc')
            ->take(5)
            ->get();

        return view('backend.index', compact(
            // Statistiques principales
            'collectesTotal',
            'facturesTotal',
            'montantTotal',
            'sitesActifs',
            'nouveauxSites',

            // Croissances
            'croissanceCollectes',
            'croissanceFactures',
            'croissanceRevenus',

            // Données graphiques
            'evolutionCollectes',
            'typesDechets',
            'evolutionMensuelle',

            // Listes
            'activitesRecentes',
            'collectesRecentes',
            'topSites',

            // Indicateurs
            'tauxValidation',
            'facturesImpayees'
        ));
    }

    /**
     * API pour mettre à jour les graphiques
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', '7days');

        switch ($period) {
            case '7days':
                $data = $this->getWeeklyData();
                break;
            case '1month':
                $data = $this->getMonthlyData();
                break;
            case '3months':
                $data = $this->getQuarterlyData();
                break;
            default:
                $data = $this->getWeeklyData();
        }

        return response()->json($data);
    }

    private function getWeeklyData()
    {
        $labels = [];
        $collectesData = [];
        $poidsData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('D');
            $collectesData[] = Collecte::whereDate('date_collecte', $date)->count();
            $poidsData[] = Collecte::whereDate('date_collecte', $date)->sum('poids');
        }

        return [
            'labels' => $labels,
            'collectes' => $collectesData,
            'poids' => $poidsData
        ];
    }

    private function getMonthlyData()
    {
        $labels = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'];
        $collectesData = [];

        for ($i = 1; $i <= 4; $i++) {
            $startWeek = Carbon::now()->startOfMonth()->addWeeks($i - 1);
            $endWeek = $startWeek->copy()->addDays(6);

            $collectesData[] = Collecte::whereBetween('date_collecte', [$startWeek, $endWeek])->count();
        }

        return [
            'labels' => $labels,
            'collectes' => $collectesData
        ];
    }

    private function getQuarterlyData()
    {
        $labels = [];
        $collectesData = [];

        for ($i = 2; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M');
            $collectesData[] = Collecte::whereMonth('date_collecte', $month->month)
                ->whereYear('date_collecte', $month->year)
                ->count();
        }

        return [
            'labels' => $labels,
            'collectes' => $collectesData
        ];
    }

    public function refreshData()
    {
        $now = Carbon::now();

        return response()->json([
            'collectesTotal' => Collecte::whereMonth('date_collecte', $now->month)
                ->whereYear('date_collecte', $now->year)
                ->count(),
            'facturesTotal' => Facture::whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->count(),
            'montantTotal' => Facture::whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('montant_facture'),
            'sitesActifs' => Site::has('collectes')->count(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Filtrage des données par type et période
     */
    public function filterData($type, $period)
    {
        $query = $this->getQueryByPeriod($period);

        switch ($type) {
            case 'collectes':
                $data = $query->from('collectes')->count();
                break;
            case 'factures':
                $data = $query->from('factures')->count();
                break;
            case 'revenus':
                $data = $query->from('factures')->sum('montant_facture');
                break;
            default:
                $data = 0;
        }

        return response()->json([
            'type' => $type,
            'period' => $period,
            'value' => $data,
            'formatted' => $type === 'revenus'
                ? number_format($data, 0, ',', ' ') . ' FCFA'
                : $data
        ]);
    }

    /**
     * Filtrage des collectes récentes
     */
    public function filterCollectes($period)
    {
        $query = Collecte::with(['site', 'typeDechet', 'agent']);

        switch ($period) {
            case 'today':
                $query->whereDate('date_collecte', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('date_collecte', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('date_collecte', Carbon::now()->month)
                    ->whereYear('date_collecte', Carbon::now()->year);
                break;
        }

        $collectes = $query->orderBy('date_collecte', 'desc')
            ->take(10)
            ->get()
            ->map(function ($collecte) {
                return [
                    'numero_collecte' => $collecte->numero_collecte,
                    'site_name' => $collecte->site->site_name,
                    'type_dechet' => $collecte->typeDechet->libelle,
                    'poids' => $collecte->poids,
                    'statut' => $collecte->statut,
                    'date_collecte' => $collecte->date_collecte->format('d/m/Y'),
                    'agent' => $collecte->agent->name ?? 'N/A'
                ];
            });

        return response()->json([
            'period' => $period,
            'collectes' => $collectes,
            'count' => $collectes->count()
        ]);
    }

    /**
     * Helper pour générer les requêtes par période
     */
    private function getQueryByPeriod($period)
    {
        $now = Carbon::now();
        $query = DB::table('');

        switch ($period) {
            case 'today':
                return $query->whereDate('created_at', $now->toDateString());
            case 'week':
                return $query->whereBetween('created_at', [
                    $now->startOfWeek()->toDateString(),
                    $now->endOfWeek()->toDateString()
                ]);
            case 'month':
                return $query->whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year);
            case 'year':
                return $query->whereYear('created_at', $now->year);
            default:
                return $query;
        }
    }
}
