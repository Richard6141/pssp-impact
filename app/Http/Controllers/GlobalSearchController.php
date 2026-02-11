<?php

namespace App\Http\Controllers;

use App\Models\Collecte;
use App\Models\Facture;
use App\Models\Observation;
use App\Models\Paiement;
use App\Models\Site;
use App\Models\TypeDechet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'query' => 'nullable|string|max:100',
        ]);

        $query = trim((string) $request->input('query', ''));
        $results = [];

        if ($query === '' || mb_strlen($query) < 2) {
            return view('search.index', compact('query', 'results'));
        }

        $results = $this->searchSections($query, 8);

        return view('search.index', compact('query', 'results'));
    }

    public function suggest(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
        ]);

        $query = trim((string) $request->input('query'));
        $sections = $this->searchSections($query, 4);
        $items = [];

        foreach ($sections as $section => $sectionItems) {
            foreach ($sectionItems as $item) {
                $item['section'] = $section;
                $items[] = $item;
            }
        }

        return response()->json([
            'items' => array_slice($items, 0, 12),
        ]);
    }

    private function searchSections(string $query, int $limitPerSection = 8): array
    {
        $results = [];
        $user = auth()->user();
        $accessibleSiteIds = $this->getAccessibleSiteIds($user);

        if ($user->can('sites.view')) {
            $sitesQuery = Site::query();
            if ($user->hasRole('Agent collecte') || $user->hasRole('Responsable site')) {
                $sitesQuery->whereIn('site_id', $accessibleSiteIds);
            }

            $sites = $sitesQuery
                ->where(function ($q) use ($query) {
                    $q->where('site_name', 'like', "%{$query}%")
                        ->orWhere('site_departement', 'like', "%{$query}%")
                        ->orWhere('site_commune', 'like', "%{$query}%")
                        ->orWhere('localisation', 'like', "%{$query}%");
                })
                ->limit($limitPerSection)
                ->get(['site_id', 'site_name', 'site_departement', 'site_commune']);

            $results['Sites'] = $sites->map(function ($item) {
                return [
                    'title' => $item->site_name,
                    'subtitle' => trim(($item->site_commune ?? '') . ' ' . ($item->site_departement ?? '')),
                    'url' => route('sites.show', $item->site_id),
                ];
            })->toArray();
        }

        if ($user->can('collectes.view')) {
            $collectesQuery = Collecte::with(['site', 'agent']);
            if ($user->hasRole('Agent collecte')) {
                $collectesQuery->where('agent_id', $user->user_id);
            } elseif ($user->hasRole('Responsable site')) {
                $collectesQuery->whereIn('site_id', $accessibleSiteIds);
            }

            $collectes = $collectesQuery
                ->where(function ($q) use ($query) {
                    $q->where('numero_collecte', 'like', "%{$query}%")
                        ->orWhere('statut', 'like', "%{$query}%");
                })
                ->orderByDesc('date_collecte')
                ->limit($limitPerSection)
                ->get();

            $results['Collectes'] = $collectes->map(function ($item) {
                return [
                    'title' => $item->numero_collecte ?? $item->collecte_id,
                    'subtitle' => ($item->site?->site_name ?? 'Sans site') . ' | ' . ($item->statut ?? 'N/A'),
                    'url' => route('collectes.show', $item->collecte_id),
                ];
            })->toArray();
        }

        if ($user->can('factures.view')) {
            $facturesQuery = Facture::with('site');
            if ($user->hasRole('Agent collecte') || $user->hasRole('Responsable site')) {
                $facturesQuery->whereIn('site_id', $accessibleSiteIds);
            }

            $factures = $facturesQuery
                ->where(function ($q) use ($query) {
                    $q->where('numero_facture', 'like', "%{$query}%")
                        ->orWhere('statut', 'like', "%{$query}%");
                })
                ->orderByDesc('date_facture')
                ->limit($limitPerSection)
                ->get();

            $results['Factures'] = $factures->map(function ($item) {
                return [
                    'title' => $item->numero_facture ?? $item->facture_id,
                    'subtitle' => ($item->site?->site_name ?? 'Sans site') . ' | ' . ($item->statut ?? 'N/A'),
                    'url' => route('factures.show', $item->facture_id),
                ];
            })->toArray();
        }

        if ($user->can('paiements.view')) {
            $paiementsQuery = Paiement::with('facture.site');
            if ($user->hasRole('Agent collecte') || $user->hasRole('Responsable site')) {
                $paiementsQuery->whereHas('facture', function ($q) use ($accessibleSiteIds) {
                    $q->whereIn('site_id', $accessibleSiteIds);
                });
            }

            $paiements = $paiementsQuery
                ->where(function ($q) use ($query) {
                    $q->where('numero_paiement', 'like', "%{$query}%")
                        ->orWhere('reference', 'like', "%{$query}%")
                        ->orWhere('mode_paiement', 'like', "%{$query}%")
                        ->orWhere('statut', 'like', "%{$query}%");
                })
                ->orderByDesc('date_paiement')
                ->limit($limitPerSection)
                ->get();

            $results['Paiements'] = $paiements->map(function ($item) {
                return [
                    'title' => $item->numero_paiement ?? $item->paiement_id,
                    'subtitle' => ($item->facture?->numero_facture ?? 'Sans facture') . ' | ' . ($item->statut ?? 'N/A'),
                    'url' => route('paiements.show', $item->paiement_id),
                ];
            })->toArray();
        }

        if ($user->can('observations.view')) {
            $observationsQuery = Observation::with('site');
            if ($user->hasRole('Agent collecte')) {
                $observationsQuery->where('user_id', $user->user_id);
            } elseif ($user->hasRole('Responsable site')) {
                $observationsQuery->whereIn('site_id', $accessibleSiteIds);
            }

            $observations = $observationsQuery
                ->where('contenu', 'like', "%{$query}%")
                ->orderByDesc('date_obs')
                ->limit($limitPerSection)
                ->get();

            $results['Observations'] = $observations->map(function ($item) {
                return [
                    'title' => 'Observation',
                    'subtitle' => ($item->site?->site_name ?? 'Sans site') . ' | ' . mb_strimwidth((string) $item->contenu, 0, 70, '...'),
                    'url' => route('observations.show', $item->observation_id),
                ];
            })->toArray();
        }

        if ($user->can('type_dechets.view')) {
            $hasCodeDbm = Schema::hasColumn('type_dechets', 'code_dbm');
            $types = TypeDechet::query()
                ->where(function ($q) use ($query, $hasCodeDbm) {
                    $q->where('libelle', 'like', "%{$query}%");
                    if ($hasCodeDbm) {
                        $q->orWhere('code_dbm', 'like', "%{$query}%");
                    }
                })
                ->limit($limitPerSection)
                ->get($hasCodeDbm ? ['type_dechet_id', 'libelle', 'code_dbm'] : ['type_dechet_id', 'libelle']);

            $results['Types de dechets'] = $types->map(function ($item) {
                return [
                    'title' => $item->libelle,
                    'subtitle' => $item->code_dbm ? ('Code DBM: ' . $item->code_dbm) : 'Sans code DBM',
                    'url' => route('type_dechets.index'),
                ];
            })->toArray();
        }

        if ($user->can('users.view')) {
            $users = User::query()
                ->where(function ($q) use ($query) {
                    $q->where('firstname', 'like', "%{$query}%")
                        ->orWhere('lastname', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%");
                })
                ->limit($limitPerSection)
                ->get(['user_id', 'firstname', 'lastname', 'email']);

            $results['Utilisateurs'] = $users->map(function ($item) {
                return [
                    'title' => trim($item->firstname . ' ' . $item->lastname),
                    'subtitle' => $item->email,
                    'url' => route('users.show', $item->user_id),
                ];
            })->toArray();
        }

        return array_filter($results, fn($items) => !empty($items));
    }

    private function getAccessibleSiteIds($user): array
    {
        $siteIds = $user->sites()->pluck('sites.site_id')->all();
        $responsableSiteIds = Site::where('responsable', $user->user_id)->pluck('site_id')->all();

        $siteIds = array_merge($siteIds, $responsableSiteIds);
        if (!empty($user->site_id)) {
            $siteIds[] = $user->site_id;
        }

        return array_values(array_unique(array_filter($siteIds)));
    }
}
