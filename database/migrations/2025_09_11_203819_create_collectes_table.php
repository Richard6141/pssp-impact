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
        Schema::create('collectes', function (Blueprint $table) {
            $table->uuid('collecte_id')->primary();
            $table->string('numero_collecte')->unique(); // ✅ numéro de collecte unique
            $table->dateTime('date_collecte');
            $table->decimal('poids', 10, 2);
            $table->uuid('type_dechet_id');
            $table->uuid('agent_id');
            $table->uuid('site_id');   // remplacé hopital_id
            $table->boolean('signature_responsable_site')->default(false);
            $table->enum('statut', ['en_attente', 'validee', 'terminee'])->default('en_attente');
            $table->boolean('isValid')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('type_dechet_id')
                ->references('type_dechet_id')
                ->on('type_dechets');

            $table->foreign('agent_id')
                ->references('user_id')
                ->on('users');

            $table->foreign('site_id')
                ->references('site_id')
                ->on('sites')
                ->onDelete('cascade');

            $table->index(['site_id', 'date_collecte']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collectes');
    }
};
