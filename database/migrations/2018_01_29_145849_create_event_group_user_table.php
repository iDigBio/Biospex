<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_group_user', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('group_id');
            $table->foreign('group_id')->references('id')->on('event_groups')->onDelete('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('event_users')->onDelete('cascade');

            $table->unique(['group_id', 'user_id'], 'event_group_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_group_user');
    }
}
