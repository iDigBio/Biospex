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
        Schema::table('geo_locate_data_sources', function (Blueprint $table) {
            $table->foreign(['expedition_id'])->references(['id'])->on('expeditions')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['geo_locate_community_id'])->references(['id'])->on('geo_locate_communities')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geo_locate_data_sources', function (Blueprint $table) {
            $table->dropForeign('geo_locate_data_sources_expedition_id_foreign');
            $table->dropForeign('geo_locate_data_sources_geo_locate_community_id_foreign');
            $table->dropForeign('geo_locate_data_sources_project_id_foreign');
        });
    }
};
