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
        if (Schema::hasTable('workflow_managers')) {
            Schema::table('workflow_managers', function (Blueprint $table) {
                $table->foreign(['expedition_id'], 'workflow_manager_expedition_id_foreign')->references(['id'])->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('workflow_managers')) {
            Schema::table('workflow_managers', function (Blueprint $table) {
                $table->dropForeign('workflow_manager_expedition_id_foreign');
            });
        }
    }
};
