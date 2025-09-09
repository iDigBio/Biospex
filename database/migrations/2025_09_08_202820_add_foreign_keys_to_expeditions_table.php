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
        if (Schema::hasTable('expeditions')) {
            Schema::table('expeditions', function (Blueprint $table) {
                $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign(['workflow_id'])->references(['id'])->on('workflows')->onUpdate('cascade')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expeditions')) {
            Schema::table('expeditions', function (Blueprint $table) {
                $table->dropForeign('expeditions_project_id_foreign');
                $table->dropForeign('expeditions_workflow_id_foreign');
            });
        }
    }
};
