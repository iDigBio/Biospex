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
        Schema::create('transcription_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('classification_id')->unique();
            $table->unsignedBigInteger('project_id')->index('transcription_locations_project_id_foreign');
            $table->unsignedBigInteger('expedition_id')->index('transcription_locations_expedition_id_foreign');
            $table->unsignedBigInteger('state_county_id')->index('transcription_locations_state_county_id_foreign');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcription_locations');
    }
};
