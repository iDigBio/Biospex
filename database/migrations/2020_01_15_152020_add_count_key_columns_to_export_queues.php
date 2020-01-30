<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountKeyColumnsToExportQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('export_queues', function (Blueprint $table) {
            $table->smallInteger('count')->after('queued');
            $table->tinyInteger('batch')->after('queued')->nullable();
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
            $table->dropColumn('count');
            $table->dropColumn('batch');
        });
    }
}
