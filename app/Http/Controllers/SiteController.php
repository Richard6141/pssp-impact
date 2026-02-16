<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Str;
use App\Services\SiteImportService;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function __construct(private SiteImportService $siteImportService)
    {
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

    public function importForm()
    {
        return view('sites.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:5120',
        ]);

        $result = $this->siteImportService->import($request->file('file'));

        $message = "Import terminé: {$result['success_count']} site(s) créé(s)";
        if ($result['error_count'] > 0) {
            $message .= " et {$result['error_count']} ligne(s) en erreur.";
        } else {
            $message .= '.';
        }

        return redirect()
            ->route('sites.import')
            ->with($result['error_count'] > 0 ? 'warning' : 'success', $message)
            ->with('import_errors', $result['errors']);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sites_import_template.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'site_code',
                'site_name',
                'site_departement',
                'site_commune',
                'localisation',
                'latitude',
                'longitude',
                'responsable_email',
            ]);
            fputcsv($handle, [
                'SIT-TG-001',
                'Clinique Baguida',
                'Maritime',
                'Lome',
                'Baguida, route nationale',
                '6.1650000',
                '1.3650000',
                'agent.collecte@example.com',
            ]);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }


    // Sauvegarde en BDD
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_code' => 'required|string|max:20|unique:sites,site_code',
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
            'site_code' => strtoupper(trim($validated['site_code'])),
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
            'site_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('sites', 'site_code')->ignore($site->site_id, 'site_id'),
            ],
            'site_name' => 'required|string|max:255',
            'site_departement' => 'required|string|max:255',
            'site_commune' => 'required|string|max:255',
            'localisation' => 'required|string|max:255',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
            'responsable' => 'nullable|exists:users,user_id',
        ]);

        $validated['site_code'] = strtoupper(trim($validated['site_code']));
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
