<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expedition_id')->index('workflow_manager_expedition_id_foreign');
            $table->tinyInteger('stopped')->default(0)->index('workflow_manager_stopped_index');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_managers');
    }
}
