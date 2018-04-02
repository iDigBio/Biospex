<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocalCountToExpeditionStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expedition_stats', function (Blueprint $table){
           $table->integer('local_subject_count')->after('expedition_id')->default(0);
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
            $table->dropColumn('local_subject_count');
        });
    }
}
