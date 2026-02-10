<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_export_comptable');
        DB::statement(
            "CREATE VIEW v_export_comptable AS
            SELECT
                f.numero_facture AS numero_facture,
                f.date_facture AS date_facture,
                f.montant_facture AS montant_facture,
                f.statut AS statut_facture,
                s.site_name AS site_name,
                u.firstname AS firstname,
                u.lastname AS lastname,
                p.numero_paiement AS numero_paiement,
                p.montant_paye AS montant_paye,
                p.date_paiement AS date_paiement,
                p.mode_paiement AS mode_paiement,
                p.statut_paiement AS statut_paiement,
                (f.montant_facture - COALESCE(p.montant_paye, 0)) AS solde_restant
            FROM factures f
            LEFT JOIN sites s ON f.site_id = s.site_id
            LEFT JOIN users u ON f.comptable_id = u.user_id
            LEFT JOIN (
                SELECT
                    facture_id,
                    SUM(montant) AS montant_paye,
                    MAX(date_paiement) AS date_paiement,
                    SUBSTRING_INDEX(GROUP_CONCAT(numero_paiement ORDER BY date_paiement DESC), ',', 1) AS numero_paiement,
                    SUBSTRING_INDEX(GROUP_CONCAT(mode_paiement ORDER BY date_paiement DESC), ',', 1) AS mode_paiement,
                    SUBSTRING_INDEX(GROUP_CONCAT(statut ORDER BY date_paiement DESC), ',', 1) AS statut_paiement
                FROM paiements
                GROUP BY facture_id
            ) p ON f.facture_id = p.facture_id"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_export_comptable');
    }
};
