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
            // Ajout des colonnes manquantes pour le profil complet
            $table->text('about')->nullable()->after('email');
            $table->string('profile_image')->nullable()->after('about');
            $table->string('company')->nullable()->after('localisation');
            $table->string('job_title')->nullable()->after('company');
            $table->string('phone')->nullable()->after('job_title');
            $table->string('address')->nullable()->after('phone');
            $table->string('country')->nullable()->after('address');
            $table->json('social_links')->nullable()->after('country');
            $table->json('settings')->nullable()->after('social_links');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'about',
                'profile_image',
                'company',
                'job_title',
                'phone',
                'address',
                'country',
                'social_links',
                'settings'
            ]);
        });
    }
};
