<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\TypeDechet;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ConfigurationController extends Controller
{

    /**
     * Afficher la page de configuration
     */
    public function index()
    {
        $sites = Site::all();
        $typesDechets = TypeDechet::all();
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $users = User::with(['roles', 'permissions'])->where('isActive', true)->get();

        return view('configurations.index', compact('sites', 'typesDechets', 'roles', 'permissions', 'users'));
    }

    // ==================== GESTION DES ROLES ====================

    /**
     * Créer un nouveau rôle
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $validated['name']]);

            if (isset($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            DB::commit();
            return redirect()->route('configuration')->with('success', 'Rôle créé avec succès!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('configuration')->with('error', 'Erreur lors de la création du rôle: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un rôle
     */
    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'admin') {
            return redirect()->route('configuration')->with('error', 'Le rôle admin ne peut pas être supprimé!');
        }

        $role->delete();
        return redirect()->route('configuration')->with('success', 'Rôle supprimé avec succès!');
    }

    // ==================== GESTION DES PERMISSIONS ====================

    /**
     * Créer une nouvelle permission
     */
    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        Permission::create(['name' => $validated['name']]);

        return redirect()->route('configuration')->with('success', 'Permission créée avec succès!');
    }

    /**
     * Supprimer une permission
     */
    public function destroyPermission($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return redirect()->route('configuration')->with('success', 'Permission supprimée avec succès!');
    }

    // ==================== ASSIGNATION DES ROLES ET PERMISSIONS ====================

    /**
     * Assigner ou révoquer des rôles et permissions à un utilisateur
     */
    public function assignRole(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'action' => 'required|in:assign,revoke',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $user = User::findOrFail($validated['user_id']);
        $action = $validated['action'];

        DB::beginTransaction();
        try {
            if ($action === 'assign') {
                // Assigner les rôles
                if (isset($validated['roles'])) {
                    foreach ($validated['roles'] as $roleName) {
                        $user->assignRole($roleName);
                    }
                }

                // Assigner les permissions directes
                if (isset($validated['permissions'])) {
                    foreach ($validated['permissions'] as $permissionName) {
                        $user->givePermissionTo($permissionName);
                    }
                }

                $message = 'Rôles et permissions assignés avec succès!';
            } else {
                // Révoquer les rôles
                if (isset($validated['roles'])) {
                    foreach ($validated['roles'] as $roleName) {
                        $user->removeRole($roleName);
                    }
                }

                // Révoquer les permissions directes
                if (isset($validated['permissions'])) {
                    foreach ($validated['permissions'] as $permissionName) {
                        $user->revokePermissionTo($permissionName);
                    }
                }

                $message = 'Rôles et permissions révoqués avec succès!';
            }

            DB::commit();
            return redirect()->route('configuration')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('configuration')->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les rôles et permissions d'un utilisateur (AJAX)
     */
    public function getUserRoles(User $user)
    {
        return response()->json([
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getDirectPermissions()->pluck('name')
        ]);
    }

    // ==================== METHODES UTILITAIRES ====================

    /**
     * Créer les permissions de base du système
     */
    public function createDefaultPermissions()
    {
        $permissions = [
            // Gestion des utilisateurs
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',

            // Gestion des sites
            'view-sites',
            'create-sites',
            'edit-sites',
            'delete-sites',

            // Gestion des déchets
            'view-dechets',
            'create-dechets',
            'edit-dechets',
            'delete-dechets',

            // Gestion des types de déchets
            'view-types-dechets',
            'create-types-dechets',
            'edit-types-dechets',
            'delete-types-dechets',

            // Rapports et statistiques
            'view-reports',
            'export-data',

            // Configuration système
            'manage-settings',
            'manage-roles',
            'assign-roles',
        ];

        DB::beginTransaction();
        try {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission]);
            }

            // Créer le rôle admin s'il n'existe pas
            $adminRole = Role::firstOrCreate(['name' => 'admin']);
            $adminRole->syncPermissions($permissions);

            DB::commit();
            return redirect()->route('configuration')->with('success', 'Permissions par défaut créées avec succès!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('configuration')->with('error', 'Erreur lors de la création des permissions: ' . $e->getMessage());
        }
    }

    /**
     * Créer des rôles par défaut
     */
    public function createDefaultRoles()
    {
        $roles = [
            'admin' => [
                'view-users',
                'create-users',
                'edit-users',
                'delete-users',
                'view-sites',
                'create-sites',
                'edit-sites',
                'delete-sites',
                'view-dechets',
                'create-dechets',
                'edit-dechets',
                'delete-dechets',
                'view-types-dechets',
                'create-types-dechets',
                'edit-types-dechets',
                'delete-types-dechets',
                'view-reports',
                'export-data',
                'manage-settings',
                'manage-roles',
                'assign-roles'
            ],
            'gestionnaire' => [
                'view-users',
                'view-sites',
                'view-dechets',
                'create-dechets',
                'edit-dechets',
                'view-types-dechets',
                'view-reports'
            ],
            'operateur' => [
                'view-sites',
                'view-dechets',
                'create-dechets',
                'view-types-dechets'
            ],
            'superviseur' => [
                'view-users',
                'view-sites',
                'view-dechets',
                'create-dechets',
                'edit-dechets',
                'view-types-dechets',
                'view-reports',
                'export-data'
            ]
        ];

        DB::beginTransaction();
        try {
            foreach ($roles as $roleName => $permissions) {
                $role = Role::firstOrCreate(['name' => $roleName]);
                $role->syncPermissions($permissions);
            }

            DB::commit();
            return redirect()->route('configuration')->with('success', 'Rôles par défaut créés avec succès!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('configuration')->with('error', 'Erreur lors de la création des rôles: ' . $e->getMessage());
        }
    }
}
