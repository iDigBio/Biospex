<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAndRemoveUniqueKeyesFromExportQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('export_queues', function (Blueprint $table) {
            $table->unique(['expedition_id', 'actor_id', 'stage']);
            $table->dropUnique('export_queues_expedition_id_actor_id_batch_unique');
            $table->dropColumn('batch');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('export_queues', function (Blueprint $table) {
            $table->tinyInteger('batch')->after('queued')->nullable();
            $table->unique(['expedition_id', 'actor_id', 'batch']);
            $table->dropUnique('export_queues_expedition_id_actor_id_stage_unique');
        });
    }
}
