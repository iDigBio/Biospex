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
        Schema::create('geo_locate_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')
                ->on('projects')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->unsignedInteger('expedition_id');
            $table->foreign('expedition_id')->references('id')
                ->on('expeditions')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->unsignedBigInteger('geo_locate_community_id');
            $table->foreign('id')->references('id')
                ->on('geo_locate_communities')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->string('data_source');
            $table->json('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_locate_stats');
    }
};
