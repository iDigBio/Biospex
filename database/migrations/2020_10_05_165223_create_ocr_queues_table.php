<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOcrQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ocr_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index('ocr_queues_project_id_foreign');
            $table->unsignedBigInteger('expedition_id')->nullable()->index('ocr_queues_expedition_id_foreign');
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0);
            $table->integer('status')->default(0);
            $table->boolean('error')->default(0);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ocr_queues');
    }
}
