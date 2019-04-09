<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusColumnToOcrQueues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocr_queues', function (Blueprint $table) {
            $table->dropColumn('mongo_id');
            $table->dropColumn('queued');
            $table->integer('status')->default(0)->after('processed');
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
            $table->dropColumn('status');
            $table->string('mongo_id')->after('expedition_id');
            $table->integer('queued')->default(0)->after('processed');
        });
    }
}
