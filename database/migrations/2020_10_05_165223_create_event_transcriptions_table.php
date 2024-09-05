<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTranscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_transcriptions', function (Blueprint $table) {
            $table->id();
            $table->integer('classification_id');
            $table->unsignedBigInteger('event_id')->index('event_transcriptions_event_id_foreign');
            $table->unsignedBigInteger('team_id')->index('event_transcriptions_team_id_foreign');
            $table->unsignedBigInteger('user_id')->index('event_transcriptions_user_id_foreign');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
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
