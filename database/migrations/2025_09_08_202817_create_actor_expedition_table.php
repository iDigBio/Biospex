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
        if (! Schema::hasTable('actor_expedition')) {
            Schema::create('actor_expedition', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('expedition_id')->index('expedition_actor_expedition_id_foreign');
                $table->unsignedBigInteger('actor_id')->index('expedition_actor_actor_id_foreign');
                $table->tinyInteger('state')->default(0);
                $table->integer('total')->default(0);
                $table->integer('error')->default(0);
                $table->integer('order')->default(0);
                $table->integer('expert')->default(0);
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
        Schema::dropIfExists('actor_expedition');
    }
};
