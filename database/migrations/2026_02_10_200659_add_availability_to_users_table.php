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
        Schema::table('users', function (Blueprint $table) {
            $table->string('availability_status')->default('offline')->after('isActive'); // available, busy, offline
            $table->json('service_communes')->nullable()->after('availability_status'); // Liste des communes desservies
            $table->json('specialties')->nullable()->after('service_communes'); // Types de déchets gérés
            $table->timestamp('last_active_at')->nullable()->after('updated_at'); // Dernière activité (pour voir qui est en ligne)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['availability_status', 'service_communes', 'specialties', 'last_active_at']);
        });
    }
};
