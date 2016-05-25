<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateAmcharts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->drop('amcharts');

        Schema::connection('mongodb')->create('amcharts', function ($collection) {
            $collection->index('project_id');
            $collection->index('expedition_id');
            $collection->index(['project_id', 'expedition_id']);
            $collection->timestamps();
            $collection->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('amcharts');
    }
}
