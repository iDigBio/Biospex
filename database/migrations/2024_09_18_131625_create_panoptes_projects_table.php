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
        Schema::create('panoptes_projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_id')->nullable()->index('panoptes_projects_project_id_foreign');
            $table->unsignedBigInteger('expedition_id')->nullable()->index('panoptes_projects_expedition_id_foreign');
            $table->integer('panoptes_project_id')->nullable();
            $table->integer('panoptes_workflow_id')->unique();
            $table->text('subject_sets')->nullable();
            $table->string('slug')->nullable();
            $table->string('title')->default('Notes From Nature');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panoptes_projects');
    }
};
