<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('downloads', function(Blueprint $table) {
            $table->enum('type', ['export', 'classifications', 'transcriptions', 'reconciled', 'summary'])->after('file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('downloads', function(Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
