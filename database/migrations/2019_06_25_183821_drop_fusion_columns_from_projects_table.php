<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropFusionColumnsFromProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('fusion_table_id');
            $table->dropColumn('fusion_style_id');
            $table->dropColumn('fusion_template_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('fusion_table_id')->nullable()->after('advertise');
            $table->integer('fusion_style_id')->after('fusion_table_id');
            $table->integer('fusion_template_id')->after('fusion_style_id');
        });
    }
}
