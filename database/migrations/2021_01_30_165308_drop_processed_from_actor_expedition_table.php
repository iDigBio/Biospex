<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropProcessedFromActorExpeditionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->dropColumn('processed');
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
            $table->integer('processed')->default(0)->index()->after('total');
        });
    }
}
