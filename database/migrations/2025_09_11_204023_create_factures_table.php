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
        Schema::create('factures', function (Blueprint $table) {
            $table->uuid('facture_id')->primary();
            $table->string('numero_facture')->unique();
            $table->date('date_facture');
            $table->decimal('montant_facture', 12, 2);
            $table->string('statut')->default('en attente');
            $table->string('photo_facture')->nullable(); // ✅ chemin de la photo
            $table->uuid('site_id');
            $table->uuid('comptable_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('comptable_id')
                ->references('user_id')
                ->on('users');

            $table->foreign('site_id')
                ->references('site_id')
                ->on('sites')
                ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
