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
        if (Schema::hasTable('panoptes_projects')) {
            Schema::table('panoptes_projects', function (Blueprint $table) {
                $table->foreign(['expedition_id'])->references(['id'])->on('expeditions')->onUpdate('no action')->onDelete('cascade');
                $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('no action')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('panoptes_projects')) {
            Schema::table('panoptes_projects', function (Blueprint $table) {
                $table->dropForeign('panoptes_projects_expedition_id_foreign');
                $table->dropForeign('panoptes_projects_project_id_foreign');
            });
        }
    }
};
