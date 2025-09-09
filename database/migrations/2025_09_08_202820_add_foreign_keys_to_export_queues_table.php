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
        if (Schema::hasTable('export_queues')) {
            Schema::table('export_queues', function (Blueprint $table) {
                $table->foreign(['actor_id'])->references(['id'])->on('actors')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign(['expedition_id'])->references(['id'])->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('export_queues')) {
            Schema::table('export_queues', function (Blueprint $table) {
                $table->dropForeign('export_queues_actor_id_foreign');
                $table->dropForeign('export_queues_expedition_id_foreign');
            });
        }
    }
};
