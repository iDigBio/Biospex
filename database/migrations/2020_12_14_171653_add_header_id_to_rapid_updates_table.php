<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHeaderIdToRapidUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rapid_updates', function (Blueprint $table) {
            $table->integer('header_id')->unsigned()->index()->nullable()->after('id');

            $table->foreign('header_id')->references('id')->on('rapid_headers')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rapid_updates', function (Blueprint $table) {
            $table->dropForeign(['header_id']);
            $table->dropColumn(['header_id']);
        });
    }
}
