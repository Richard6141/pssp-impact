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
        Schema::create('site_user', function (Blueprint $table) {
            $table->id();
            
            // Relation utilisateur (UUID)
            $table->string('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');

            // Relation site (UUID)
            $table->string('site_id');
            $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');

            // Metadata de l'assignation
            $table->boolean('is_primary')->default(false); // Est-ce le site principal ?
            
            $table->unique(['user_id', 'site_id']); // Éviter les doublons
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_user');
    }
};
