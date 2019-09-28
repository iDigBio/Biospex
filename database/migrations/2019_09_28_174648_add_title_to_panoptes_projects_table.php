<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleToPanoptesProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('panoptes_projects', function (Blueprint $table) {
            $table->string('title')->after('slug')->default('Notes From Nature');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('panoptes_project', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
}
