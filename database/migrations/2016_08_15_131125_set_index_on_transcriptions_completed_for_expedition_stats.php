<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetIndexOnTranscriptionsCompletedForExpeditionStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expedition_stats', function ($table) {
            $table->index('transcriptions_completed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expedition_stats', function ($table) {
            $table->dropIndex('expedition_stats_transcriptions_completed_index');
        });
    }
}
