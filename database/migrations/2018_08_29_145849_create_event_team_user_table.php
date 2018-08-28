<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTeamUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('event_team_user')) {
            Schema::create('event_team_user', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('team_id');
                $table->foreign('team_id')->references('id')->on('event_teams')->onDelete('cascade');

                $table->unsignedInteger('user_id');
                $table->foreign('user_id')->references('id')->on('event_teams')->onDelete('cascade');

                $table->unique(['team_id', 'user_id'], 'event_team_user');
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
        Schema::dropIfExists('event_team_user');
    }
}
