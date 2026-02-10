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
        Schema::create('role_permissions_matrix', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->string('module'); // collectes, factures, sites, rapports, etc.
            $table->json('permissions'); // ['view', 'create', 'update', 'delete', 'validate']
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['role_name', 'module']);
            $table->index('module');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions_matrix');
    }
};
