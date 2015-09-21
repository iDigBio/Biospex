<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductIdIdIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->collection('subjectdocs', function (Blueprint $collection) {
            $collection->index('id');
            $collection->index('project_id');
            $collection->unique(['project_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->collection('subjectdocs', function (Blueprint $collection) {
            $collection->dropIndex('id');
            $collection->dropIndex('project_id');
            $collection->dropIndex(['project_id', 'id']);
        });
    }
}
