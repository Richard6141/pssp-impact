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
        Schema::create('rapports', function (Blueprint $table) {
            $table->uuid('rapport_id')->primary();
            $table->string('periode');
            $table->string('type_rapport'); // mensuel, annuel, site, client
            $table->longText('contenu');
            $table->uuid('site_id');   // remplacé hopital_id
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};