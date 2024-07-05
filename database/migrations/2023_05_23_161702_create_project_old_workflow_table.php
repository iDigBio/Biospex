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
        Schema::dropIfExists('project_old_workflow');

        Schema::create('project_old_workflow', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('project_id');
            $table->tinyInteger('workflow_id');
        });
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
