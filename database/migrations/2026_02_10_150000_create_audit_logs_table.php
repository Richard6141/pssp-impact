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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('audit_id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('action'); // create, update, delete, login, logout
            $table->string('entity_type'); // Collecte, Site, Facture, etc.
            $table->string('entity_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable(); // Anciennes valeurs
            $table->json('new_values')->nullable(); // Nouvelles valeurs
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
            
            // Index pour performance
            $table->index(['user_id', 'performed_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'performed_at']);
            
            // Foreign key (soft constraint)
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
