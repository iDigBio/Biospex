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
        Schema::table('export_queues', function (Blueprint $table) {
            $table->tinyInteger('files_ready')->default(0)->after('total');
            $table->index(['error', 'queued', 'files_ready']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('export_queues', function (Blueprint $table) {
            $table->dropIndex(['error', 'queued', 'files_ready']);
            $table->dropColumn('files_ready');
        });
    }
};
