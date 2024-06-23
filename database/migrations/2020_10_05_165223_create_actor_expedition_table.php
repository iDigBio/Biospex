<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorExpeditionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actor_expedition', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expedition_id')->index('expedition_actor_expedition_id_foreign');
            $table->unsignedBigInteger('actor_id')->index('expedition_actor_actor_id_foreign');
            $table->tinyInteger('state')->default(0);
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0)->index();
            $table->integer('error')->default(0);
            $table->integer('queued')->default(0);
            $table->integer('completed')->default(0);
            $table->integer('order')->default(0);
            $table->integer('expert')->default(0);
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
        Schema::dropIfExists('actor_expedition');
    }
}
