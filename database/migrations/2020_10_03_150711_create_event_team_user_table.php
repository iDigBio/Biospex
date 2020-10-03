<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTeamUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_team_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('team_id')->index('event_team_user_team_id_foreign');
            $table->unsignedInteger('user_id')->index('event_team_user_user_id_foreign');
        });
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
