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
        Schema::table('type_dechets', function (Blueprint $table) {
            $table->string('code')->unique()->nullable()->after('type_dechet_id');
        });
    }

    public function down(): void
    {
        Schema::table('type_dechets', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
