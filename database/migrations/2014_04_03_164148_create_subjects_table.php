<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedInteger('header_id');
            $table->foreign('header_id')->references('id')->on('headers')->onDelete('cascade');
            $table->unsignedInteger('meta_id');
            $table->foreign('meta_id')->references('id')->on('metas')->onDelete('cascade');
            $table->string('mongo_id');
            $table->string('object_id');
            $table->unique(array('project_id', 'object_id'));
            $table->index('header_id');
            $table->index('meta_id');
            $table->timestamps();
            $table->softDeletes();

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
        Schema::drop('subjects');
    }
}
