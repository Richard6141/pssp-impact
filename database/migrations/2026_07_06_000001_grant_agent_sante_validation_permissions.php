<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Demande client PSSP-IMPACT+ (29/06/2026) : autoriser le profil
 * « Agent santé » à valider les quantités de DBM enlevées, afin de
 * débloquer le processus quand le responsable du site est indisponible.
 *
 * Migration idempotente : applique en production les permissions déjà
 * ajoutées au RolesSeeder, sans nécessiter un re-seed complet.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        'collectes.validate_site',
        'validations.view',
        'validations.create',
    ];

    public function up(): void
    {
        $role = Role::where('name', 'Agent santé')->first();

        if (!$role) {
            return; // Base vierge : le seeder fera le travail
        }

        foreach (self::PERMISSIONS as $name) {
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $role = Role::where('name', 'Agent santé')->first();

        if (!$role) {
            return;
        }

        foreach (self::PERMISSIONS as $name) {
            $role->revokePermissionTo($name);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
