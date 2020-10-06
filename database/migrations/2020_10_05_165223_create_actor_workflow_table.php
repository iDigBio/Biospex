<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorWorkflowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actor_workflow', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workflow_id')->index('actor_workflow_workflow_id_foreign');
            $table->unsignedInteger('actor_id')->index('actor_workflow_actor_id_foreign');
            $table->integer('order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('actor_workflow');
    }
}
