<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartDateToExpeditionStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expedition_stats', function (Blueprint $table) {
            $table->timestamp('start_date')->after('percent_completed')->nullable();
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
            $table->dropColumn('start_date');
        });
    }
}
