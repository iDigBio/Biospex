<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToActorExpeditionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->integer('total')->default(0)->after('state');
            $table->integer('processed')->default(0)->after('total');
            $table->index('processed');
        });
        // DELETE FROM migrations WHERE batch = 31
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->dropIndex('actor_expedition_processed_index');
            $table->dropColumn('total');
            $table->dropColumn('processed');
        });
    }
}
