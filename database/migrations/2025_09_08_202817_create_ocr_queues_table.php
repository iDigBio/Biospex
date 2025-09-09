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
        if (! Schema::hasTable('ocr_queues')) {
            Schema::create('ocr_queues', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('project_id')->index('ocr_queues_project_id_foreign');
                $table->unsignedBigInteger('expedition_id')->nullable()->index('ocr_queues_expedition_id_foreign');
                $table->integer('total')->default(0);
                $table->integer('status')->default(0);
                $table->boolean('error')->default(false);
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_queues');
    }
};
