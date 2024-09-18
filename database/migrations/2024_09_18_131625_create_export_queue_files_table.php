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
        Schema::create('export_queue_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('queue_id')->index('export_queue_files_queue_id_foreign');
            $table->string('subject_id', 30)->nullable()->unique();
            $table->string('access_uri')->nullable();
            $table->string('message')->nullable();
            $table->boolean('processed')->default(false);
            $table->tinyInteger('tries')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_queue_files');
    }
};
