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
        Schema::create('wedigbio_event_transcriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('classification_id')->unique();
            $table->unsignedBigInteger('project_id')->index('wedigbio_event_transcriptions_project_id_foreign');
            $table->unsignedBigInteger('date_id')->index('wedigbio_event_transcriptions_date_id_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedigbio_event_transcriptions');
    }
};
