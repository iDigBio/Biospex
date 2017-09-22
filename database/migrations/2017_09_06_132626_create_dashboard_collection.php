<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('wedigbio_dashboard', function ($collection) {
            $collection->index('transcription_id');
            $collection->index('project_uuid');
            $collection->index('expedition_uuid');
            $collection->unique('guid');
            $collection->index('timestamp');
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('wedigbio_dashboard');
    }
}
