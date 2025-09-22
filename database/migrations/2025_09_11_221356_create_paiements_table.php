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
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('paiement_id')->primary();
            $table->uuid('facture_id');
            $table->string('numero_paiement')->unique();
            $table->decimal('montant', 12, 2);
            $table->string('mode_paiement');
            $table->date('date_paiement');
            $table->string('reference')->nullable();
            $table->string('paiement_photo')->nullable();
            $table->string('statut')->default('en attente');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('facture_id')->references('facture_id')->on('factures')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
