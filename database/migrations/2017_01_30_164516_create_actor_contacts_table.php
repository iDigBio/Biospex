<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActorContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actor_contacts', function ($table) {
            $table->increments('id');
            $table->integer('actor_id')->unsigned();
            $table->string('email');
            $table->timestamps();

            $table->foreign('actor_id')->references('id')->on('actors')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::drop('actor_contacts');
    }
}
