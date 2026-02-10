<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('token')->unique();
            
            // Rôle attribué (stocké par ID ou nom selon votre implémentation spatie/permission)
            // Ici on suppose l'utilisation de spatie/laravel-permission avec des IDs
            $table->unsignedBigInteger('role_id')->nullable();

            // Qui a envoyé l'invitation (User ID est un UUID string)
            $table->string('inviter_id')->nullable();
            $table->foreign('inviter_id')->references('user_id')->on('users')->nullOnDelete();

            $table->timestamp('registered_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            // Index pour recherche rapide
            $table->index(['email', 'token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
    }
};
