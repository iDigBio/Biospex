<?php

use Illuminate\Database\Migrations\Migration;

class ChangeWorkflowToTitleOnWorkflowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workflows', function ($table) {
            $table->renameColumn('workflow', 'title');
            $table->unique('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workflows', function ($table) {
            $table->renameColumn('title', 'workflow');
            $table->unique('workflow');
        });
    }
}
