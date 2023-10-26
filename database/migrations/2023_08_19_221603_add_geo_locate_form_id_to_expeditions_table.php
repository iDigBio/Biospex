<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expeditions', function (Blueprint $table) {
            $table->unsignedInteger('geo_locate_form_id')
                ->index('expeditions_geo_locate_form_id_foreign')
                ->after('workflow_id')
                ->nullable();
            $table->foreign('geo_locate_form_id')
                ->references('id')
                ->on('geo_locate_forms')->onDelete('set null');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expeditions', function (Blueprint $table) {
            $table->dropForeign(['geo_locate_form_id']);
            $table->dropColumn('geo_locate_form_id');
        });
    }
};
