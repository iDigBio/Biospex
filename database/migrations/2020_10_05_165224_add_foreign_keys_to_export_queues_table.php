<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToExportQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('export_queues', function (Blueprint $table) {
            $table->foreign('actor_id')->references('id')->on('actors')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('CASCADE')->onDelete('CASCADE');
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
            $table->dropForeign('queues.exports_actor_id_foreign');
            $table->dropForeign('queues.exports_expedition_id_foreign');
        });
    }
}
