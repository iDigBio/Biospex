<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropErrorOcrQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocr_queues', function (Blueprint $table) {
            $table->dropColumn('error');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocr_queues', function (Blueprint $table) {
            $table->integer('error')->default(0)->index()->after('status');
        });
    }
}
