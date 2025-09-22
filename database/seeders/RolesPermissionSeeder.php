<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // === GESTION DES UTILISATEURS ===
            ['name' => 'users.view', 'libelle' => 'Consulter les utilisateurs'],
            ['name' => 'users.create', 'libelle' => 'Créer un utilisateur'],
            ['name' => 'users.update', 'libelle' => 'Modifier un utilisateur'],
            ['name' => 'users.delete', 'libelle' => 'Supprimer un utilisateur'],
            ['name' => 'users.activate', 'libelle' => 'Activer/Désactiver un utilisateur'],
            ['name' => 'users.assign_roles', 'libelle' => 'Assigner un rôle à un utilisateur'],
            ['name' => 'users.export', 'libelle' => 'Exporter les utilisateurs'],
            ['name' => 'users.stats', 'libelle' => 'Voir les statistiques utilisateurs'],

            // === GESTION DES SITES ===
            ['name' => 'sites.view', 'libelle' => 'Consulter les sites'],
            ['name' => 'sites.create', 'libelle' => 'Créer un site'],
            ['name' => 'sites.update', 'libelle' => 'Modifier un site'],
            ['name' => 'sites.delete', 'libelle' => 'Supprimer un site'],

            // === GESTION DES TYPES DE DÉCHETS ===
            ['name' => 'type_dechets.view', 'libelle' => 'Consulter les types de déchets'],
            ['name' => 'type_dechets.create', 'libelle' => 'Créer un type de déchet'],
            ['name' => 'type_dechets.update', 'libelle' => 'Modifier un type de déchet'],
            ['name' => 'type_dechets.delete', 'libelle' => 'Supprimer un type de déchet'],

            // === GESTION DES COLLECTES ===
            ['name' => 'collectes.view', 'libelle' => 'Consulter les collectes'],
            ['name' => 'collectes.create', 'libelle' => 'Enregistrer une collecte'],
            ['name' => 'collectes.update', 'libelle' => 'Modifier une collecte'],
            ['name' => 'collectes.delete', 'libelle' => 'Supprimer une collecte'],
            ['name' => 'collectes.validate_site', 'libelle' => 'Valider une collecte au niveau du site'],
            ['name' => 'collectes.validate_final', 'libelle' => 'Validation finale des collectes'],
            ['name' => 'collectes.report_incident', 'libelle' => 'Signaler un incident de collecte'],

            // === GESTION DES VALIDATIONS ===
            ['name' => 'validations.view', 'libelle' => 'Consulter les validations'],
            ['name' => 'validations.create', 'libelle' => 'Créer une validation'],
            ['name' => 'validations.delete', 'libelle' => 'Supprimer une validation'],

            // === GESTION DES INCIDENTS ===
            ['name' => 'incidents.view', 'libelle' => 'Consulter les incidents'],
            ['name' => 'incidents.create', 'libelle' => 'Déclarer un incident'],
            ['name' => 'incidents.update', 'libelle' => 'Mettre à jour un incident'],
            ['name' => 'incidents.resolve', 'libelle' => 'Résoudre un incident'],

            // === GESTION DES OBSERVATIONS ===
            ['name' => 'observations.view', 'libelle' => 'Consulter les observations'],
            ['name' => 'observations.create', 'libelle' => 'Ajouter une observation'],
            ['name' => 'observations.delete', 'libelle' => 'Supprimer une observation'],

            // === GESTION DES FACTURES ===
            ['name' => 'factures.view', 'libelle' => 'Consulter les factures'],
            ['name' => 'factures.create', 'libelle' => 'Créer une facture'],
            ['name' => 'factures.update', 'libelle' => 'Modifier une facture'],
            ['name' => 'factures.delete', 'libelle' => 'Supprimer une facture'],
            ['name' => 'factures.send', 'libelle' => 'Envoyer une facture au client'],

            // === GESTION DES PAIEMENTS ===
            ['name' => 'paiements.view', 'libelle' => 'Consulter les paiements'],
            ['name' => 'paiements.record', 'libelle' => 'Enregistrer un paiement'],
            ['name' => 'paiements.update', 'libelle' => 'Modifier un paiement'],
            ['name' => 'paiements.delete', 'libelle' => 'Supprimer un paiement'],
            ['name' => 'paiements.validate', 'libelle' => 'Valider un paiement'],

            // === GESTION DES RAPPORTS ===
            ['name' => 'rapports.view', 'libelle' => 'Consulter les rapports'],
            ['name' => 'rapports.generate', 'libelle' => 'Générer un rapport'],
            ['name' => 'rapports.export', 'libelle' => 'Exporter un rapport'],
            ['name' => 'rapports.collectes', 'libelle' => 'Rapport des collectes'],
            ['name' => 'rapports.financier', 'libelle' => 'Rapport financier'],
            ['name' => 'rapports.sites', 'libelle' => 'Rapport par sites'],

            // === GESTION DES CONFIGURATIONS ===
            ['name' => 'configurations.view', 'libelle' => 'Consulter les configurations'],
            ['name' => 'configurations.update', 'libelle' => 'Modifier les configurations'],

            // === RÔLES ET PERMISSIONS ===
            ['name' => 'roles.view', 'libelle' => 'Consulter les rôles'],
            ['name' => 'roles.create', 'libelle' => 'Créer un rôle'],
            ['name' => 'roles.update', 'libelle' => 'Modifier un rôle'],
            ['name' => 'roles.delete', 'libelle' => 'Supprimer un rôle'],
            ['name' => 'permissions.view', 'libelle' => 'Consulter les permissions'],
            ['name' => 'permissions.assign', 'libelle' => 'Assigner des permissions'],
            ['name' => 'permissions.revoke', 'libelle' => 'Révoquer des permissions'],

            // === SYSTÈME ===
            ['name' => 'system.settings', 'libelle' => 'Modifier les paramètres système'],
            ['name' => 'system.logs', 'libelle' => 'Consulter les logs système'],
            ['name' => 'system.backup', 'libelle' => 'Gérer les sauvegardes'],
            ['name' => 'system.maintenance', 'libelle' => 'Activer le mode maintenance'],
            ['name' => 'system.info', 'libelle' => 'Consulter les informations système'],
            ['name' => 'system.database', 'libelle' => 'Gérer la base de données'],
            ['name' => 'system.health', 'libelle' => 'Vérifier la santé du système'],
            ['name' => 'system.commands', 'libelle' => 'Exécuter des commandes système'],

            // === PERMISSIONS SPÉCIFIQUES MÉTIER ===
            ['name' => 'data.own_site_only', 'libelle' => 'Accès limité aux données de son propre site'],
            ['name' => 'data.all_sites', 'libelle' => 'Accès aux données de tous les sites'],
            ['name' => 'export.pdf', 'libelle' => 'Exporter en PDF'],
            ['name' => 'export.excel', 'libelle' => 'Exporter en Excel'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'name' => $permission['name'],
                    'libelle' => $permission['libelle'],
                    'guard_name' => 'web'
                ]
            );
        }

        $this->command->info('Permissions créées avec succès !');
    }
}
