<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les collecteurs saisissent des poids au gramme pres (ex. 0.125 kg) mais la
     * colonne etait en decimal(10,2) : MySQL arrondissait silencieusement la valeur
     * a l'insertion. On elargit a 3 decimales (retour terrain du 10/08/2026).
     *
     * Elargissement non destructif : les valeurs deja enregistrees sont conservees.
     */
    public function up(): void
    {
        Schema::table('collectes', function (Blueprint $table) {
            $table->decimal('poids', 12, 3)->change();
        });
    }

    public function down(): void
    {
        Schema::table('collectes', function (Blueprint $table) {
            $table->decimal('poids', 10, 2)->change();
        });
    }
};
