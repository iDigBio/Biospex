<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocalTranscriptionsCompleteToExpeditionStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expedition_stats', function (Blueprint $table) {
            //$table->integer('local_transcriptions_completed')->default(0)->after('transcriptions_total');
            //$table->renameColumn('transcriptions_total', 'transcriptions_goal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expedition_stats', function (Blueprint $table) {
            //$table->dropColumn('local_transcriptions_completed');
            //$table->renameColumn('transcriptions_goal', 'transcriptions_total');
        });
    }
}
