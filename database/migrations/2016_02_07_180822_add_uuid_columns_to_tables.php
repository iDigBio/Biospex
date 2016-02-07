<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUuidColumnsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE users ADD uuid BINARY(16) NULL AFTER id");
        DB::statement("ALTER TABLE groups ADD uuid BINARY(16) NULL AFTER id");
        DB::statement("ALTER TABLE downloads ADD uuid BINARY(16) NULL AFTER id");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('downloads', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}
