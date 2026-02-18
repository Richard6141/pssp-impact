<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_invitations', function (Blueprint $table) {
            $table->string('site_id')->nullable()->after('role_id');
            $table->boolean('assign_as_site_responsable')->default(false)->after('site_id');

            $table->foreign('site_id')
                ->references('site_id')
                ->on('sites')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_invitations', function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn(['site_id', 'assign_as_site_responsable']);
        });
    }
};
