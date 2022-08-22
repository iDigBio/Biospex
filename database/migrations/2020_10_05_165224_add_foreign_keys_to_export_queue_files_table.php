<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToExportQueueFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('queues.export_files', function (Blueprint $table) {
            $table->foreign('queue_id')->references('id')->on('queues.exports')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('queues.export_files', function (Blueprint $table) {
            $table->dropForeign('queues.export_files_queue_id_foreign');
        });
    }
}
