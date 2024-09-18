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
        Schema::table('transcription_locations', function (Blueprint $table) {
            $table->foreign(['expedition_id'])->references(['id'])->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['state_county_id'])->references(['id'])->on('state_counties')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transcription_locations', function (Blueprint $table) {
            $table->dropForeign('transcription_locations_expedition_id_foreign');
            $table->dropForeign('transcription_locations_project_id_foreign');
            $table->dropForeign('transcription_locations_state_county_id_foreign');
        });
    }
};
