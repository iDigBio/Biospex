<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->foreign('actor_id')->references('id')->on('actors')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropForeign('downloads_actor_id_foreign');
            $table->dropForeign('downloads_expedition_id_foreign');
        });
    }
}
