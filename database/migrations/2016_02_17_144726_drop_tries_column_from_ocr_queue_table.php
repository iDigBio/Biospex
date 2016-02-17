<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTriesColumnFromOcrQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop
        Schema::table('ocr_queues', function (Blueprint $table) {
            $table->dropColumn(['tries']);
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
            $table->integer('tries')->after('subject_remaining')->default(0);
        });
    }
}
