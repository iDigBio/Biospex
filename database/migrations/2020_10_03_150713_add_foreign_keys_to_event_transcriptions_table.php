<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEventTranscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('event_transcriptions', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('team_id')->references('id')->on('event_teams')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('event_users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_transcriptions', function (Blueprint $table) {
            $table->dropForeign('event_transcriptions_event_id_foreign');
            $table->dropForeign('event_transcriptions_team_id_foreign');
            $table->dropForeign('event_transcriptions_user_id_foreign');
        });
    }
}
