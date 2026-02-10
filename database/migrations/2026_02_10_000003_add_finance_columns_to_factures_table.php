<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->date('date_echeance')->nullable()->after('date_facture');
            $table->decimal('tva', 5, 2)->default(0)->after('deleted_at');
            $table->decimal('montant_ht', 15, 4)->nullable()->after('tva');
            $table->decimal('montant_tva', 15, 4)->nullable()->after('montant_ht');
            $table->decimal('montant_ttc', 15, 4)->nullable()->after('montant_tva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['date_echeance', 'tva', 'montant_ht', 'montant_tva', 'montant_ttc']);
        });
    }
};
