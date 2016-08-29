<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToDownloads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('downloads', function ($table) {
            $table->string('file')->change();
            $table->unique('file');
            $table->index(['expedition_id', 'actor_id', 'file']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('downloads', function ($table) {
            $table->dropIndex('downloads_file');
            $table->dropIndex('downloads_expedition_id_actor_id_file');
        });
    }
}
