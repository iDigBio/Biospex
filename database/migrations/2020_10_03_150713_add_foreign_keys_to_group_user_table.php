<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->foreign('group_id', 'users_groups_group_id_foreign')->references('id')->on('groups')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id', 'users_groups_user_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_user', function (Blueprint $table) {
            $table->dropForeign('users_groups_group_id_foreign');
            $table->dropForeign('users_groups_user_id_foreign');
        });
    }
}
