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
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('incident_id')->primary();
            $table->uuid('collecte_id');
            $table->uuid('reported_by');
            $table->text('description');
            $table->dateTime('date_incident');
            $table->string('statut')->default('ouvert');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('collecte_id')->references('collecte_id')->on('collectes')->onDelete('cascade');
            $table->foreign('reported_by')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};