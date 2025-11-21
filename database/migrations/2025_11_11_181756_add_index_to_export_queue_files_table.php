<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('export_queue_files', function (Blueprint $table) {
            $table->dropUnique('export_queue_files_subject_id_unique');
            $table->unique(['queue_id', 'subject_id']);
            $table->index(['queue_id', 'processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('export_queue_files', function (Blueprint $table) {
            $table->dropUnique(['queue_id', 'subject_id']);
            $table->dropIndex(['queue_id', 'processed']);
        });
    }
};
