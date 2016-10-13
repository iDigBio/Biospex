<?php

use Illuminate\Database\Migrations\Migration;

class ChangeSubjectCountSubjectRemainingActorExpeditionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (table_has_index('ocr_queues', 'ocr_queues_subject_count_index'))
        {
            Schema::table('ocr_queues', function ($table) {
                $table->dropIndex('ocr_queues_subject_count_index');
            });
        }

        if (table_has_index('ocr_queues', 'ocr_queues_subject_remaining_index'))
        {
            Schema::table('ocr_queues', function ($table) {
                $table->dropIndex('ocr_queues_subject_remaining_index');
            });
        }


        Schema::table('ocr_queues', function ($table) {
            $table->renameColumn('subject_count', 'total');
            $table->renameColumn('subject_remaining', 'processed');
            $table->index('total');
            $table->index('processed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocr_queues', function ($table) {
            $table->dropIndex('ocr_queues_total_index');
            $table->dropIndex('ocr_queues_processed_index');

            $table->renameColumn('total', 'subject_count');
            $table->renameColumn('processed', 'subject_remaining');

            $table->index('subject_count');
            $table->index('subject_remaining');
        });
    }
}
