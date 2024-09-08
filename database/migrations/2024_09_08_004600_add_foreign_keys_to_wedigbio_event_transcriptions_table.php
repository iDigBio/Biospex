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
        Schema::table('wedigbio_event_transcriptions', function (Blueprint $table) {
            $table->foreign(['date_id'])->references(['id'])->on('wedigbio_event_dates')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['project_id'])->references(['id'])->on('projects')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wedigbio_event_transcriptions', function (Blueprint $table) {
            $table->dropForeign('wedigbio_event_transcriptions_date_id_foreign');
            $table->dropForeign('wedigbio_event_transcriptions_project_id_foreign');
        });
    }
};
