<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTranscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('event_transcriptions')) {
            Schema::create('event_transcriptions', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('classification_id');
                $table->unsignedInteger('event_id');
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->unsignedInteger('team_id');
                $table->foreign('team_id')->references('id')->on('event_teams')->onDelete('cascade');
                $table->unsignedInteger('user_id');
                $table->foreign('user_id')->references('id')->on('event_users')->onDelete('cascade');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_transcriptions');
    }
}
