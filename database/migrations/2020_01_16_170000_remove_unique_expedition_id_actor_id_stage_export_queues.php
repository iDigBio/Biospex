<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUniqueExpeditionIdActorIdStageExportQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('export_queues', function(Blueprint $table)
        {
            $table->unique(['expedition_id', 'actor_id', 'batch']);
            $table->dropUnique('export_queues_expedition_id_actor_id_stage_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('export_queues', function(Blueprint $table)
        {
            $table->unique(['expedition_id', 'actor_id', 'stage']);
            $table->dropUnique('export_queues_expedition_id_actor_id_batch_unique');
        });
    }
}
