<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.view')->only(['index', 'show']);
        $this->middleware('permission:users.create')->only(['create', 'store']);
        $this->middleware('permission:users.update')->only(['edit', 'update']);
        $this->middleware('permission:users.delete')->only(['destroy']);
        $this->middleware('permission:users.assign_roles')->only(['assignRole', 'removeRole']);
    }

    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'site']);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('isActive', $isActive);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        $users = $query->paginate(20);

        // DonnÃ©es pour les filtres
        $roles = Role::all();
        $sites = Site::select('site_id', 'site_name')->get();

        return view('users.index', compact('users', 'roles', 'sites'));
    }

    /**
     * Afficher le formulaire de crÃ©ation
     */
    public function create()
    {
        $roles = Role::all();
        $sites = Site::select('site_id', 'site_name')->get();

        return view('users.create', compact('roles', 'sites'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'localisation' => 'nullable|string|max:255',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'about' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'role' => 'required|exists:roles,name',
            'site_id' => 'nullable|exists:sites,site_id',
            'sites' => 'nullable|array',
            'sites.*' => 'exists:sites,site_id',
            'set_as_site_responsable' => 'nullable|boolean',
            'isActive' => 'nullable|boolean'
        ]);

        if ($request->boolean('set_as_site_responsable') && !$request->filled('site_id')) {
            return back()
                ->withInput()
                ->withErrors(['site_id' => 'Selectionnez un site principal avant de definir un responsable.']);
        }

        $userData = $request->except(['password', 'password_confirmation', 'role', 'profile_image', 'sites']);
        $userData['password'] = Hash::make($request->password);
        $userData['isActive'] = $request->boolean('isActive');

        // Upload de l'image de profil
        if ($request->hasFile('profile_image')) {
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user = User::create($userData);

        // Assigner le rÃ´le
        $user->assignRole($request->role);

        // Assigner les sites (Many-to-Many)
        $siteIds = collect($request->input('sites', []))
            ->filter()
            ->values();

        if ($request->filled('site_id')) {
            $siteIds->push($request->site_id);
        }

        $siteIds = $siteIds->unique()->values();
        $syncData = [];
        foreach ($siteIds as $siteId) {
            $syncData[$siteId] = ['is_primary' => $request->filled('site_id') && $siteId === $request->site_id];
        }

        $user->sites()->sync($syncData);
        $assignedSitesCount = $user->sites()->count();
        Log::info('Affectation sites utilisateur (store)', [
            'user_id' => $user->user_id,
            'site_id_principal' => $request->site_id,
            'sites_ids' => $siteIds->toArray(),
            'assigned_count' => $assignedSitesCount,
        ]);

        if ($request->boolean('set_as_site_responsable') && $request->filled('site_id')) {
            Site::where('site_id', $request->site_id)->update(['responsable' => $user->user_id]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur cree avec succes. Sites affectes: ' . $assignedSitesCount);
    }

    /**
     * Afficher les dÃ©tails d'un utilisateur
     */
    public function show(User $user)
    {
        $user->load(['roles', 'permissions', 'site']);

        // Statistiques de l'utilisateur
        $stats = [];

        if ($user->hasRole('Agent collecte')) {
            $stats['collectes'] = [
                'total' => $user->collectes()->count(),
                'ce_mois' => $user->collectes()->whereMonth('created_at', now()->month)->count(),
                'validees' => $user->collectes()->where('isValid', true)->count(),
            ];
        }

        if ($user->hasRole('Comptable')) {
            $stats['factures'] = [
                'total' => $user->factures()->count(),
                'ce_mois' => $user->factures()->whereMonth('created_at', now()->month)->count(),
                'montant_total' => $user->factures()->sum('montant_facture'),
            ];
        }

        if ($user->hasRole(['Responsable site', 'Agent santÃ©'])) {
            $stats['observations'] = [
                'total' => $user->observations()->count(),
                'ce_mois' => $user->observations()->whereMonth('created_at', now()->month)->count(),
            ];
        }

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Afficher le formulaire d'Ã©dition
     */
    public function edit(User $user)
    {
        $user->load('sites');
        $roles = Role::all();
        $sites = Site::select('site_id', 'site_name')->get();
        $userRoles = $user->getRoleNames();

        return view('users.edit', compact('user', 'roles', 'sites', 'userRoles'));
    }

    /**
     * Mettre Ã  jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => 'nullable|string|min:8',
            'localisation' => 'nullable|string|max:255',
            'longitude' => 'nullable|numeric|between:-180,180',
            'latitude' => 'nullable|numeric|between:-90,90',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'about' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'role' => 'required|exists:roles,name',
            'site_id' => 'nullable|exists:sites,site_id',
            'sites' => 'nullable|array',
            'sites.*' => 'exists:sites,site_id',
            'set_as_site_responsable' => 'nullable|boolean',
            'isActive' => 'nullable|boolean'
        ]);

        if ($request->boolean('set_as_site_responsable') && !$request->filled('site_id')) {
            return back()
                ->withInput()
                ->withErrors(['site_id' => 'Selectionnez un site principal avant de definir un responsable.']);
        }

        $userData = $request->except(['password', 'password_confirmation', 'role', 'profile_image', 'sites']);
        $userData['isActive'] = $request->boolean('isActive');

        // Mise Ã  jour du mot de passe si fourni
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        // Upload de la nouvelle image de profil
        if ($request->hasFile('profile_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user->update($userData);

        // Mettre Ã  jour le rÃ´le
        $user->syncRoles([$request->role]);

        // Mettre Ã  jour les sites (Many-to-Many)
        $siteIds = collect($request->input('sites', []))
            ->filter()
            ->values();

        if ($request->filled('site_id')) {
            $siteIds->push($request->site_id);
        }

        $siteIds = $siteIds->unique()->values();
        $syncData = [];
        foreach ($siteIds as $siteId) {
            $syncData[$siteId] = ['is_primary' => $request->filled('site_id') && $siteId === $request->site_id];
        }

        $user->sites()->sync($syncData);
        $assignedSitesCount = $user->sites()->count();
        Log::info('Affectation sites utilisateur (update)', [
            'user_id' => $user->user_id,
            'site_id_principal' => $request->site_id,
            'sites_ids' => $siteIds->toArray(),
            'assigned_count' => $assignedSitesCount,
        ]);

        if ($request->boolean('set_as_site_responsable') && $request->filled('site_id')) {
            Site::where('site_id', $request->site_id)->update(['responsable' => $user->user_id]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis a jour avec succes. Sites affectes: ' . $assignedSitesCount);
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // VÃ©rifier si l'utilisateur peut Ãªtre supprimÃ©
        $hasRelations = [
            'collectes' => $user->collectes()->count(),
            'factures' => $user->factures()->count(),
            'observations' => $user->observations()->count(),
            'incidents' => $user->incidents()->count(),
            'validations' => $user->validations()->count(),
            'responsable_sites' => $user->responsableSites()->count(),
        ];

        if (array_sum($hasRelations) > 0) {
            return redirect()->route('users.index')
                ->with('error', 'Impossible de supprimer cet utilisateur car il a des donnÃ©es associÃ©es.');
        }

        // Supprimer l'image de profil
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimÃ© avec succÃ¨s.');
    }

    /**
     * Activer/DÃ©sactiver un utilisateur
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('users.activate');

        $user->update(['isActive' => !$user->isActive]);

        $status = $user->isActive ? 'activÃ©' : 'dÃ©sactivÃ©';

        return response()->json([
            'success' => true,
            'message' => "Utilisateur {$status} avec succÃ¨s",
            'status' => $user->isActive
        ]);
    }

    /**
     * Recherche d'utilisateurs (pour autocomplete)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $users = User::where('firstname', 'like', "%{$query}%")
            ->orWhere('lastname', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get(['user_id', 'firstname', 'lastname', 'email']);

        return response()->json([
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->user_id,
                    'text' => $user->firstname . ' ' . $user->lastname . ' (' . $user->email . ')'
                ];
            })
        ]);
    }

    /**
     * Assigner un rÃ´le Ã  un utilisateur
     */
    public function assignRole(Request $request, User $user)
    {
        $this->authorize('users.assign_roles');

        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'RÃ´le assignÃ© avec succÃ¨s'
        ]);
    }

    /**
     * Retirer un rÃ´le d'un utilisateur
     */
    public function removeRole(Request $request, User $user)
    {
        $this->authorize('users.assign_roles');

        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->removeRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'RÃ´le retirÃ© avec succÃ¨s'
        ]);
    }

    /**
     * RÃ©initialiser le mot de passe
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('users.update');

        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe rÃ©initialisÃ© avec succÃ¨s'
        ]);
    }

    /**
     * Export des utilisateurs
     */
    public function export(Request $request)
    {
        $this->authorize('users.view');

        $users = User::with(['roles', 'site'])->get();

        $filename = 'utilisateurs_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // En-tÃªtes CSV
            fputcsv($file, [
                'ID',
                'PrÃ©nom',
                'Nom',
                'Username',
                'Email',
                'TÃ©lÃ©phone',
                'Entreprise',
                'Poste',
                'Site',
                'RÃ´le(s)',
                'Statut',
                'Date crÃ©ation',
                'DerniÃ¨re connexion'
            ]);

            // DonnÃ©es
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->user_id,
                    $user->firstname,
                    $user->lastname,
                    $user->username,
                    $user->email,
                    $user->phone,
                    $user->company,
                    $user->job_title,
                    $user->site ? $user->site->site_name : '',
                    $user->getRoleNames()->implode(', '),
                    $user->isActive ? 'Actif' : 'Inactif',
                    $user->created_at->format('d/m/Y H:i'),
                    $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Statistiques des utilisateurs
     */
    public function stats()
    {
        $this->authorize('users.view');

        $stats = [
            'total' => User::count(),
            'active' => User::where('isActive', true)->count(),
            'inactive' => User::where('isActive', false)->count(),
            'recent' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        // RÃ©partition par rÃ´le
        $roleStats = Role::withCount('users')->get();

        // Utilisateurs rÃ©cents (7 derniers jours)
        $recentUsers = User::with('roles')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('users.stats', compact('stats', 'roleStats', 'recentUsers'));
    }
}

