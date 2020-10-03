<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToActorWorkflowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actor_workflow', function (Blueprint $table) {
            $table->foreign('actor_id')->references('id')->on('actors')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('workflow_id')->references('id')->on('workflows')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actor_workflow', function (Blueprint $table) {
            $table->dropForeign('actor_workflow_actor_id_foreign');
            $table->dropForeign('actor_workflow_workflow_id_foreign');
        });
    }
}
