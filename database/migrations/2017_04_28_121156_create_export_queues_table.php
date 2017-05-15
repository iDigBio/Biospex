<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_queues', function ($table) {
            $table->increments('id');
            $table->integer('expedition_id')->unsigned();
            $table->integer('stage');
            $table->boolean('queued');
            $table->longText('missing')->nullable();
            $table->timestamps();

            $table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('export_queues');
    }
}
