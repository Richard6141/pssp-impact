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
        Schema::create('observations', function (Blueprint $table) {
            $table->uuid('observation_id')->primary();
            $table->uuid('site_id');
            $table->uuid('user_id');
            $table->text('contenu');
            $table->dateTime('date_obs');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};