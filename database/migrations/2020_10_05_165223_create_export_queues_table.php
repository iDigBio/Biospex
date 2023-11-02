<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExportQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_queues', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('expedition_id');
            $table->unsignedInteger('actor_id')->index('exports_actor_id_foreign');
            $table->integer('stage')->default(0)->index();
            $table->boolean('queued')->default(0)->index();
            $table->smallInteger('count');
            $table->boolean('error')->default(0)->index();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->unique(['expedition_id', 'actor_id', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('export_queues');
    }
}
