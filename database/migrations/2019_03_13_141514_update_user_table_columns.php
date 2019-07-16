<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserTableColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->after('password')->nullable();
            $table->dropColumn('deleted_at');
        });

        DB::statement("UPDATE `users` SET email_verified_at=NOW() WHERE activated=1");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('activated');
            $table->dropColumn('activation_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });
    }
}
