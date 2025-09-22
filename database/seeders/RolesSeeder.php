<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run()
    {
        // Définition des rôles avec leurs permissions
        $rolesPermissions = [
            // === SUPER ADMIN (vous) ===
            'Super Admin' => 'all', // Toutes les permissions

            // === AGENT DE COLLECTE ===
            'Agent collecte' => [
                // Collectes
                'collectes.view',
                'collectes.create',
                'collectes.update',
                'collectes.report_incident',

                // Observations
                'observations.create',
                'observations.view',

                // Sites
                'sites.view',

                // Incidents
                'incidents.create',
                'incidents.view',

                // Types de déchets (consultation)
                'type_dechets.view',

                // Accès limité à ses données
                'data.own_site_only',
            ],

            // === RESPONSABLE DE SITE (côté client) ===
            'Responsable site' => [
                // Collectes (limité à son site)
                'collectes.view',
                'collectes.validate_site',

                // Validations
                'validations.view',
                'validations.create',

                // Observations
                'observations.view',
                'observations.create',

                // Sites (son site uniquement)
                'sites.view',

                // Incidents (consultation)
                'incidents.view',

                // Accès limité à son site
                'data.own_site_only',
            ],

            // === AGENT DE SANTÉ / TECHNICIEN HYGIÈNE ===
            'Agent santé' => [
                // Observations et suggestions
                'observations.create',
                'observations.view',

                // Collectes (consultation de son site)
                'collectes.view',

                // Sites (son site)
                'sites.view',

                // Incidents (consultation et déclaration)
                'incidents.view',
                'incidents.create',

                // Accès limité à son site
                'data.own_site_only',
            ],

            // === COORDONNATEUR ===
            'Coordonnateur' => [
                // Gestion complète des collectes
                'collectes.view',
                'collectes.create',
                'collectes.update',
                'collectes.validate_site',
                'collectes.validate_final',

                // Gestion des sites
                'sites.view',
                'sites.create',
                'sites.update',
                'sites.delete',

                // Types de déchets
                'type_dechets.view',
                'type_dechets.create',
                'type_dechets.update',

                // Gestion des validations
                'validations.view',
                'validations.create',
                'validations.delete',

                // Gestion des incidents et observations
                'incidents.view',
                'incidents.create',
                'incidents.update',
                'incidents.resolve',
                'observations.view',
                'observations.create',
                'observations.delete',

                // Accès aux factures et paiements (consultation)
                'factures.view',
                'paiements.view',

                // Rapports complets
                'rapports.view',
                'rapports.generate',
                'rapports.export',
                'rapports.collectes',
                'rapports.sites',

                // Gestion des utilisateurs (agents)
                'users.view',
                'users.create',
                'users.update',
                'users.assign_roles',

                // Accès à tous les sites
                'data.all_sites',

                // Export
                'export.pdf',
                'export.excel',
            ],

            // === COMPTABLE ===
            'Comptable' => [
                // Gestion complète des factures
                'factures.view',
                'factures.create',
                'factures.update',
                'factures.delete',
                'factures.send',

                // Gestion complète des paiements
                'paiements.view',
                'paiements.record',
                'paiements.update',
                'paiements.delete',
                'paiements.validate',

                // Consultation des collectes pour facturation
                'collectes.view',

                // Consultation des sites pour facturation
                'sites.view',

                // Rapports financiers
                'rapports.view',
                'rapports.generate',
                'rapports.export',
                'rapports.financier',
                'rapports.collectes',

                // Accès à tous les sites
                'data.all_sites',

                // Export
                'export.pdf',
                'export.excel',
            ],

            // === AGENT MARKETING ===
            'Agent marketing' => [
                // Accès en lecture aux factures par site/mois
                'factures.view',

                // Suivi du recouvrement
                'paiements.view',

                // Rapports de suivi
                'rapports.view',
                'rapports.generate',
                'rapports.financier',
                'rapports.sites',

                // Consultation des sites clients
                'sites.view',

                // Consultation des collectes pour analyse
                'collectes.view',

                // Accès à tous les sites
                'data.all_sites',

                // Export
                'export.pdf',
                'export.excel',
            ],

            // === ADMINISTRATEUR TECHNIQUE ===
            'Administrateur' => [
                // Gestion des utilisateurs
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'users.activate',
                'users.assign_roles',
                'users.export',
                'users.stats',

                // Gestion des rôles et permissions
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',
                'permissions.view',
                'permissions.assign',
                'permissions.revoke',

                // Gestion des configurations
                'configurations.view',
                'configurations.update',

                // Gestion du système (partiel)
                'system.settings',
                'system.logs',
                'system.info',
                'system.health',

                // Accès à toutes les données (lecture)
                'sites.view',
                'collectes.view',
                'factures.view',
                'paiements.view',
                'rapports.view',
                'rapports.generate',
                'observations.view',
                'incidents.view',

                // Types de déchets
                'type_dechets.view',
                'type_dechets.create',
                'type_dechets.update',
                'type_dechets.delete',

                // Accès à tous les sites
                'data.all_sites',

                // Export
                'export.pdf',
                'export.excel',
            ],
        ];

        // Création des rôles et assignation des permissions
        foreach ($rolesPermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if ($permissions === 'all') {
                // Pour le Super Admin, assigner toutes les permissions
                $role->syncPermissions(Permission::all());
            } elseif (is_array($permissions)) {
                // Vérifier que toutes les permissions existent avant de les assigner
                $existingPermissions = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
                $missingPermissions = array_diff($permissions, $existingPermissions);

                if (!empty($missingPermissions)) {
                    $this->command->warn("Permissions manquantes pour le rôle {$roleName}: " . implode(', ', $missingPermissions));
                }

                $role->syncPermissions($existingPermissions);
            }
        }

        $this->command->info('Rôles et permissions assignés avec succès !');

        // Affichage des rôles créés avec comptage des permissions
        $this->command->info('Rôles créés :');
        foreach (array_keys($rolesPermissions) as $roleName) {
            $role = Role::where('name', $roleName)->first();
            $permissionCount = $role ? $role->permissions()->count() : 0;
            $this->command->line("- {$roleName} ({$permissionCount} permissions)");
        }
    }
}
