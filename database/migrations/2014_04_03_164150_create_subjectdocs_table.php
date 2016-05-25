<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSubjectdocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->drop('subjectdocs');

        Schema::connection('mongodb')->create('subjectdocs', function ($collection) {
            $collection->index('project_id');
            $collection->index('subject_id');
            $collection->unique(array('project_id', 'subject_id'));
            $collection->timestamps();
            $collection->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('subjectdocs');
    }
}
