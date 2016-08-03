<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQueuedToAmChartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('amcharts', function (Blueprint $table) {
            $table->longText('raw')->after('data');
            $table->tinyInteger('queued')->default(0)->after('raw');
            $table->index('queued');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('amcharts', function (Blueprint $table) {
            $table->dropColumn('raw');
            $table->dropIndex('amcharts_queued_index');
            $table->dropColumn('queued');
        });
    }
}
