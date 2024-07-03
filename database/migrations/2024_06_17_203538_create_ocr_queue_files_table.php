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
        Schema::create('ocr_queue_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('queue_id')->index('ocr_files_queue_id_foreign');
            $table->string('subject_id', 30)->nullable()->unique();
            $table->string('access_uri')->nullable();
            $table->boolean('processed')->default(0);
            $table->tinyInteger('tries')->default(0);
            $table->timestamps();
        });

        Schema::table('ocr_queue_files', function (Blueprint $table) {
            $table->foreign('queue_id')->references('id')->on('ocr_queues')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ocr_queue_files', function (Blueprint $table) {
            $table->dropForeign(['queue_id']);
        });

        Schema::dropIfExists('ocr_queue_files');
    }
};
