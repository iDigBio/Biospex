<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDataJsonAmcharts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('amcharts', function (Blueprint $table) {
            $table->dropColumn('raw');
            $table->json('data')->nullable()->change();
            $table->json('series')->after('project_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('amcharts', function (Blueprint $table) {
            $table->longText('data')->nullable()->change();
            $table->dropColumn('series');
            $table->longText('raw')->after('data')->nullable();
        });
    }
}
