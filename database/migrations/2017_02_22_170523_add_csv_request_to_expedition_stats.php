<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCsvRequestToExpeditionStats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expedition_stats', function (Blueprint $table) {
            $table->integer('classification_process')->default(0)->after('percent_completed');
            $table->index('classification_process');
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
            $table->dropIndex('classification_process');
            $table->dropColumn('classification_process');
        });
    }
}
