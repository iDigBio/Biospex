<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_old_workflow', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('project_id');
            $table->tinyInteger('workflow_id');
        });

        Artisan::call('update:queries moveProjectWorkflow');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_old_workflow');
    }
};
