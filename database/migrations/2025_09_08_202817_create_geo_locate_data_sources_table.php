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
        if (! Schema::hasTable('geo_locate_data_sources')) {
            Schema::create('geo_locate_data_sources', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('uuid', 36)->unique();
                $table->unsignedBigInteger('project_id')->index('geo_locate_data_sources_project_id_foreign');
                $table->unsignedBigInteger('expedition_id')->index('geo_locate_data_sources_expedition_id_foreign');
                $table->unsignedBigInteger('geo_locate_form_id')->index('geo_locate_data_sources_geo_locate_form_id_foreign');
                $table->unsignedBigInteger('geo_locate_community_id')->nullable()->index('geo_locate_data_sources_geo_locate_community_id_foreign');
                $table->unsignedBigInteger('download_id')->nullable()->index('geo_locate_data_sources_download_id_foreign');
                $table->string('data_source')->nullable();
                $table->json('data')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_locate_data_sources');
    }
};
