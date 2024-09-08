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
        Schema::table('ocr_queues', function (Blueprint $table) {
            $table->foreign(['expedition_id'])->references(['id'])->on('expeditions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocr_queues', function (Blueprint $table) {
            $table->dropForeign('ocr_queues_expedition_id_foreign');
            $table->dropForeign('ocr_queues_project_id_foreign');
        });
    }
};
