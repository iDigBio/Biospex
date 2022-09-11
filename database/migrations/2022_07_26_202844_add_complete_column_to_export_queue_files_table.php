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
        Schema::table('export_queue_files', function (Blueprint $table) {
            $table->tinyInteger('completed')->default(0)->index()->after('error');
            $table->dropColumn('error');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('export_queue_files', function (Blueprint $table) {
            $table->tinyInteger('error')->default(0)->index()->after('url');
            $table->dropColumn('completed');
        });
    }
};
