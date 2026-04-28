<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->index('statut', 'idx_factures_statut');
            $table->index('date_facture', 'idx_factures_date_facture');
            $table->index(['site_id', 'statut'], 'idx_factures_site_statut');
            $table->index(['site_id', 'date_facture'], 'idx_factures_site_date');
        });

        Schema::table('collectes', function (Blueprint $table) {
            $table->index('isValid', 'idx_collectes_isvalid');
            $table->index('statut', 'idx_collectes_statut');
            $table->index('signature_responsable_site', 'idx_collectes_signature');
            $table->index(['site_id', 'isValid'], 'idx_collectes_site_isvalid');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropIndex('idx_factures_statut');
            $table->dropIndex('idx_factures_date_facture');
            $table->dropIndex('idx_factures_site_statut');
            $table->dropIndex('idx_factures_site_date');
        });

        Schema::table('collectes', function (Blueprint $table) {
            $table->dropIndex('idx_collectes_isvalid');
            $table->dropIndex('idx_collectes_statut');
            $table->dropIndex('idx_collectes_signature');
            $table->dropIndex('idx_collectes_site_isvalid');
        });
    }
};
