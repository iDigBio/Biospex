<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wedigbio_event_transcriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('classification_id')->unique();
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('CASCADE');
            $table->unsignedInteger('date_id');
            $table->foreign('date_id')->references('id')->on('wedigbio_event_dates')->onDelete('CASCADE');
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
        Schema::dropIfExists('wedigbio_event_transcriptions');
    }
};
