<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameWedigbiodashboardToPusherTranscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $db = config('database.connections.mongodb.database');

        DB::connection('mongodb')->getMongoClient()->admin->command([
            'renameCollection' => "{$db}.wedigbio_dashboard",
            'to' => "{$db}.pusher_transcriptions",
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $db = config('database.connections.mongodb.database');

        DB::connection('mongodb')->getMongoClient()->admin->command([
            'renameCollection' => "{$db}.pusher_transcriptions",
            'to' => "{$db}.wedigbio_dashboard",
        ]);
    }
}
