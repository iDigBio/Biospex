<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'persist_code',
                'reset_password_code',
                'permissions',
                'last_login',
                'activated_at']
            );
        });

        // Add
        Schema::table('users', function (Blueprint $table) {
            $table->rememberToken();
        });
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
