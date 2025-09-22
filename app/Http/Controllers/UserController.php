<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;

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

        // Données pour les filtres
        $roles = Role::all();
        $sites = Site::select('site_id', 'site_name')->get();

        return view('users.index', compact('users', 'roles', 'sites'));
    }

    /**
     * Afficher le formulaire de création
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
            'isActive' => 'boolean'
        ]);

        $userData = $request->except(['password', 'password_confirmation', 'role', 'profile_image']);
        $userData['password'] = Hash::make($request->password);
        $userData['isActive'] = $request->has('isActive');

        // Upload de l'image de profil
        if ($request->hasFile('profile_image')) {
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user = User::create($userData);

        // Assigner le rôle
        $user->assignRole($request->role);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Afficher les détails d'un utilisateur
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

        if ($user->hasRole(['Responsable site', 'Agent santé'])) {
            $stats['observations'] = [
                'total' => $user->observations()->count(),
                'ce_mois' => $user->observations()->whereMonth('created_at', now()->month)->count(),
            ];
        }

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $sites = Site::select('site_id', 'site_name')->get();
        $userRoles = $user->getRoleNames();

        return view('users.edit', compact('user', 'roles', 'sites', 'userRoles'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => 'nullable|string|min:8|confirmed',
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
            'isActive' => 'boolean'
        ]);

        $userData = $request->except(['password', 'password_confirmation', 'role', 'profile_image']);
        $userData['isActive'] = $request->has('isActive');

        // Mise à jour du mot de passe si fourni
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

        // Mettre à jour le rôle
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // Vérifier si l'utilisateur peut être supprimé
        if ($user->collectes()->count() > 0 || $user->factures()->count() > 0) {
            return redirect()->route('users.index')
                ->with('error', 'Impossible de supprimer cet utilisateur car il a des données associées.');
        }

        // Supprimer l'image de profil
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('users.activate');

        $user->update(['isActive' => !$user->isActive]);

        $status = $user->isActive ? 'activé' : 'désactivé';

        return response()->json([
            'success' => true,
            'message' => "Utilisateur {$status} avec succès",
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
     * Assigner un rôle à un utilisateur
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
            'message' => 'Rôle assigné avec succès'
        ]);
    }

    /**
     * Retirer un rôle d'un utilisateur
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
            'message' => 'Rôle retiré avec succès'
        ]);
    }

    /**
     * Réinitialiser le mot de passe
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
            'message' => 'Mot de passe réinitialisé avec succès'
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

            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Prénom',
                'Nom',
                'Username',
                'Email',
                'Téléphone',
                'Entreprise',
                'Poste',
                'Site',
                'Rôle(s)',
                'Statut',
                'Date création',
                'Dernière connexion'
            ]);

            // Données
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

        // Répartition par rôle
        $roleStats = Role::withCount('users')->get();

        // Utilisateurs récents (7 derniers jours)
        $recentUsers = User::with('roles')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('users.stats', compact('stats', 'roleStats', 'recentUsers'));
    }
}
