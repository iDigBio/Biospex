<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePanoptesTranscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mongodb')->create('panoptes_transcriptions', function ($collection) {
            $collection->unique('classification_id');
            $collection->index('workflow_id');
            $collection->index('subject_projectId');
            $collection->index('subject_expeditionId');
            $collection->index('subject_subjectId');
            $collection->index('classification_finished_at');
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mongodb')->drop('panoptes_transcriptions');
    }
}
