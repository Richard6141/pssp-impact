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
        Schema::create('facture_collectes', function (Blueprint $table) {
            $table->uuid('factureCollecte_id')->primary();
            $table->uuid('facture_id');
            $table->uuid('collecte_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('facture_id')->references('facture_id')->on('factures')->onDelete('cascade');
            $table->foreign('collecte_id')->references('collecte_id')->on('collectes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facture_collectes');
    }
};