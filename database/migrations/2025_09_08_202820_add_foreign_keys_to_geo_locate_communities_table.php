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
        if (Schema::hasTable('geo_locate_communities')) {
            Schema::table('geo_locate_communities', function (Blueprint $table) {
                $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('restrict')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('geo_locate_communities')) {
            Schema::table('geo_locate_communities', function (Blueprint $table) {
                $table->dropForeign('geo_locate_communities_project_id_foreign');
            });
        }
    }
};
