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
        Schema::create('export_queues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('expedition_id');
            $table->unsignedBigInteger('actor_id')->index('export_queues_actor_id_foreign');
            $table->integer('stage')->default(0)->index();
            $table->boolean('queued')->default(false)->index();
            $table->smallInteger('total');
            $table->boolean('error')->default(false)->index();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->unique(['expedition_id', 'actor_id', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_queues');
    }
};
