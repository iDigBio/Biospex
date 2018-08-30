<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('event_teams')) {
            Schema::create('event_teams', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('event_id');
                $table->string('title');
                $table->timestamps();

                $table->unique(['event_id', 'title'], 'event_team_title');
            });

            DB::statement("ALTER TABLE event_teams ADD uuid BINARY(16) NULL AFTER id");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_teams');
    }
}
