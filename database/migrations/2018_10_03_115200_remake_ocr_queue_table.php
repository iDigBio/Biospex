<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemakeOcrQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ocr_queues');
        Schema::dropIfExists('ocr_csv');

        Schema::create('ocr_queues', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('expedition_id')->nullable();
            $table->string('mongo_id')->unique();
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0);
            $table->boolean('queued')->default(0);
            $table->boolean('error')->default(0);
            $table->text('csv')->nullable();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade')->nullable();

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
        Schema::dropIfExists('ocr_queues');
        Schema::dropIfExists('ocr_csv');
    }
}
