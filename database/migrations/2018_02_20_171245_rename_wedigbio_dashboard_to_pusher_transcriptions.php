<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameWedigbioDashboardToPusherTranscriptions extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->collection('pusher_transcriptions', function (Blueprint $collection) {
            $collection->index('contributer.transcriber');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->collection('pusher_transcriptions', function (Blueprint $collection) {
            $collection->dropIndex('contributer.transcriber');
        });
    }
}
