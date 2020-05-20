<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBingosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bingos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index('bingos_user_id_foreign');
            $table->integer('project_id')->unsigned()->index('bingos_project_id_foreign');
            $table->string('title', 20);
            $table->string('directions', 256);
            $table->string('contact', 191);
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
        Schema::dropIfExists('bingos');
    }
}
