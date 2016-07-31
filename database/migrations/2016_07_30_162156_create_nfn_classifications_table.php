<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNfnClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nfn_classifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('classification_id');
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unsignedInteger('expedition_id');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
            $table->timestamp('finished_at');
            $table->timestamps();
            $table->softDeletes();

            $table->engine = 'InnoDB';
            $table->unique('classification_id');
            $table->index(['project_id', 'finished_at']);
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('nfn_classifications');
    }
}
