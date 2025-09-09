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
        if (Schema::hasTable('project_resources')) {
            Schema::table('project_resources', function (Blueprint $table) {
                $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('no action')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_resources')) {
            Schema::table('project_resources', function (Blueprint $table) {
                $table->dropForeign('project_resources_project_id_foreign');
            });
        }
    }
};
