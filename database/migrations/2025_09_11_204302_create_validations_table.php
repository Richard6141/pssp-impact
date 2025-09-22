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
        Schema::create('validations', function (Blueprint $table) {
            $table->uuid('validation_id')->primary();
            $table->uuid('collecte_id');
            $table->uuid('validated_by'); // user_id du responsable
            $table->string('type_validation'); // ex: "responsable_site" ou "chef_comptable"
            $table->dateTime('date_validation');
            $table->text('commentaire')->nullable();
            $table->string('signature')->nullable(); // chemin fichier signature
            $table->enum('statut', ['en_attente', 'validee', 'rejetee'])->default('en_attente');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('collecte_id')->references('collecte_id')->on('collectes')->onDelete('cascade');
            $table->foreign('validated_by')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('validations');
    }
};
