<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropPermissionsFromGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['permissions']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->string('label')->after('name');
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
