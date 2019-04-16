<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOcrFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ocr_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('queue_id');
            $table->foreign('queue_id')->references('id')->on('ocr_queues')->onDelete('cascade');
            $table->string('subject_id');
            $table->text('messages')->nullable();
            $table->text('ocr')->nullable();
            $table->boolean('status')->default(0);
            $table->string('url');
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
        Schema::dropIfExists('ocr_files');
    }
}
