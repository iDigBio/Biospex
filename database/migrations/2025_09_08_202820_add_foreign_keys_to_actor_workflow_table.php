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
        if (Schema::hasTable('actor_workflow')) {
            Schema::table('actor_workflow', function (Blueprint $table) {
                $table->foreign(['actor_id'])->references(['id'])->on('actors')->onUpdate('no action')->onDelete('cascade');
                $table->foreign(['workflow_id'])->references(['id'])->on('workflows')->onUpdate('no action')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('actor_workflow')) {
            Schema::table('actor_workflow', function (Blueprint $table) {
                $table->dropForeign('actor_workflow_actor_id_foreign');
                $table->dropForeign('actor_workflow_workflow_id_foreign');
            });
        }
    }
};
