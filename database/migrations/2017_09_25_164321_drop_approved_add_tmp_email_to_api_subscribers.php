<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropApprovedAddTmpEmailToApiSubscribers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_subscribers', function(Blueprint $table) {
            $table->dropIndex('api_subscribers_approved_index');
            $table->dropColumn('approved');
            $table->string('tmp_email')->after('email');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_subscribers', function(Blueprint $table) {
            $table->integer('approved')->default(0)->after('remember_token');
            $table->index('approved');
            $table->dropColumn('tmp_email');
        });
    }
}
