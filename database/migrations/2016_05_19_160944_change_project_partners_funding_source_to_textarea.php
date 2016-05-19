<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeProjectPartnersFundingSourceToTextarea extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE projects MODIFY project_partners TEXT NOT NULL;");
        DB::statement("ALTER TABLE projects MODIFY funding_source TEXT NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE projects MODIFY project_partners VARCHAR(255) NOT NULL;");
        DB::statement("ALTER TABLE projects MODIFY funding_source VARCHAR(255) NOT NULL;");
    }
}
