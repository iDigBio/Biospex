<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterStatusToTinyIntegerOnOcrQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `ocr_queues` CHANGE `status` `status` TINYINT(1) DEFAULT '0';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `ocr_queues` CHANGE `status` `status` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;");
    }
}
