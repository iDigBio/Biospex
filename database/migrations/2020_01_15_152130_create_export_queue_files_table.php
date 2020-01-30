<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportQueueFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_queue_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('queue_id')->unsigned();
            $table->string('subject_id', 30)->nullable()->unique();
            $table->string('url')->nullable();
            $table->tinyInteger('error')->default(0);
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('export_queue_files');
    }
}
