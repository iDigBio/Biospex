<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRapidRecordsCollection extends Migration
{
    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::connection($this->connection)->hasCollection('rapid_records')) {
            Schema::connection($this->connection)->create('rapid_records', function ($collection) {
                $collection->unique(['gbifID_gbifR', 'idigbio_uuid_idbP']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection($this->connection)->collection('rapid_records', function ($collection) {
            $collection->drop();
        });
    }
}
