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
        Schema::create('ecritures_comptables', function (Blueprint $table) {
            $table->uuid('ecriture_id')->primary();
            $table->date('date_ecriture');
            $table->string('numero_piece');
            $table->enum('type_piece', ['facture', 'paiement', 'avoir', 'charge']);
            $table->uuid('piece_id');
            $table->string('compte_debit', 20);
            $table->string('compte_credit', 20);
            $table->text('libelle');
            $table->decimal('montant', 15, 4);
            $table->string('devise', 3)->default('XOF');
            $table->uuid('user_id');
            $table->timestamps();

            $table->index(['type_piece', 'piece_id']);
            $table->index('user_id');
            $table->foreign('user_id')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecritures_comptables');
    }
};
