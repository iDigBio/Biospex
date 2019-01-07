<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDataToJsonAmcharts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('amcharts', 'raw'))
        {
            Schema::table('amcharts', function (Blueprint $table) {
                $table->dropColumn('raw');
            });
        }

        Schema::table('amcharts', function (Blueprint $table) {
            //$table->json('data')->nullable()->change();
            $table->longText('series')->nullable()->after('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('amcharts', 'series'))
        {
            Schema::table('amcharts', function (Blueprint $table) {
                $table->dropColumn('series');
            });
        }

        Schema::table('amcharts', function (Blueprint $table) {
            $table->longText('data')->nullable()->change();
        });
    }
}
