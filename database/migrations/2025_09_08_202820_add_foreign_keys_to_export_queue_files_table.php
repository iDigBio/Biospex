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
        if (Schema::hasTable('export_queue_files')) {
            Schema::table('export_queue_files', function (Blueprint $table) {
                $table->foreign(['queue_id'])->references(['id'])->on('export_queues')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('export_queue_files')) {
            Schema::table('export_queue_files', function (Blueprint $table) {
                $table->dropForeign('export_queue_files_queue_id_foreign');
            });
        }
    }
};
