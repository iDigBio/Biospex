<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ocr_queues', function (Blueprint $table) {
            // 1. Drop old column
            $table->dropColumn('status');

            // 2. Add new columns (exactly like export_queues)
            $table->boolean('queued')->default(true)->after('expedition_id');
            $table->boolean('files_ready')->default(0)->after('queued');
        });

        // Second: ocr_queue_files index fix
        Schema::table('ocr_queue_files', function (Blueprint $table) {
            // Drop the old unique index on subject_id only
            $table->dropUnique('ocr_queue_files_subject_id_unique');

            // Add new composite unique index: one subject per queue only
            $table->unique(['queue_id', 'subject_id'], 'ocr_queue_files_queue_id_subject_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ocr_queues', function (Blueprint $table) {
            $table->dropColumn(['queued', 'files_ready']);
            $table->tinyInteger('status')->default(0)->after('expedition_id');
        });

        Schema::table('ocr_queue_files', function (Blueprint $table) {
            $table->dropUnique('ocr_queue_files_queue_id_subject_id_unique');
            $table->unique('subject_id', 'ocr_queue_files_subject_id_unique');
        });
    }
};
