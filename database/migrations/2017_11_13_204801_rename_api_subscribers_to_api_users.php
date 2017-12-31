<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameApiSubscribersToApiUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_subscribers', function(Blueprint $table) {
            $table->dropColumn('tmp_email');
            $table->boolean('activated')->default(0)->after('password');
        });
        Schema::rename('api_subscribers', 'api_users');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_users', function(Blueprint $table) {
            $table->string('tmp_email')->after('email');
            $table->dropColumn('activated');
        });
        Schema::rename('api_users', 'api_subscribers');
    }
}
