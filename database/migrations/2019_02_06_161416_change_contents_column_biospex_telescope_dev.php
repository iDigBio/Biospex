<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeContentsColumnBiospexTelescopeDev extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::connection('telescope')->hasTable('telescope_entries'))
        {
            Schema::connection('telescope')->table('telescope_entries', function(Blueprint $table)
            {
                $table->longText('content')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::connection('telescope')->hasTable('telescope_entries')) {
            Schema::connection('telescope')->table('telescope_entries', function (Blueprint $table) {
                $table->text('content')->change();
            });
        }
    }
}
