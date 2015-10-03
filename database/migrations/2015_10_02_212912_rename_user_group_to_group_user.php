<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameUserGroupToGroupUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('users_groups', 'group_user');
        Schema::rename('expedition_actor', 'actor_expedition');
        Schema::rename('ocr_queue', 'ocr_queues');
        Schema::rename('project_actor', 'actor_project');
        Schema::rename('workflow_manager', 'workflow_managers');
        Schema::rename('user_grid_fields', 'user_grid_fields');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
