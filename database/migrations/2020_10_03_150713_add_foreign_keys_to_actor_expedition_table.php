<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToActorExpeditionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->foreign('actor_id', 'expedition_actor_actor_id_foreign')->references('id')->on('actors')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('expedition_id', 'expedition_actor_expedition_id_foreign')->references('id')->on('expeditions')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->dropForeign('expedition_actor_actor_id_foreign');
            $table->dropForeign('expedition_actor_expedition_id_foreign');
        });
    }
}
