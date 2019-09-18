<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveIndexMakeNullableForNfnWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('nfn_workflows', function (Blueprint $table) {
            $table->dropForeign(['expedition_id']);
            $table->dropUnique('nfn_workflows_expedition_id_unique');
            $table->unsignedInteger('project_id')->nullable()->change();
            $table->unsignedInteger('expedition_id')->nullable()->change();
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('RESTRICT')->onDelete('CASCADE');

            $table->renameColumn('project', 'panoptes_project_id');
            $table->renameColumn('workflow', 'panoptes_workflow_id');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('nfn_workflows', function (Blueprint $table) {
            $table->unsignedInteger('project_id')->nullable(false)->change();
            $table->unsignedInteger('expedition_id')->nullable(false)->change();
        });
        Schema::enableForeignKeyConstraints();
    }
}
