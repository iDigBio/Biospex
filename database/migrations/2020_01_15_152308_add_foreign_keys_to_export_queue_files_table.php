<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToExportQueueFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('export_queue_files', function (Blueprint $table) {
            $table->foreign('queue_id')->references('id')->on('export_queues')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('export_queue_files', function (Blueprint $table) {
            $table->dropForeign('export_queue_files_queue_id_foreign');
        });
    }
}
