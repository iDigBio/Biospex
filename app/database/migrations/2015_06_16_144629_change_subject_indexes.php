<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSubjectIndexes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->collection('subjects', function(Blueprint $collection)
        {
            $collection->dropIndex('project_id');
            $collection->index(['project_id', 'occurrence.id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->collection('subjects', function(Blueprint $collection)
        {
            $collection->dropIndex('project_occurrence');
            $collection->index('project_id');
        });
    }

}
