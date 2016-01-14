<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupPermissionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_permission', function (Blueprint $table) {
            $table->integer('group_id')->unsigned()->index();
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->integer('permission_id')->unsigned()->index();
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

            $table->primary(['group_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('group_permission');
    }

}
