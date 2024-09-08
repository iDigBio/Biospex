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
        Schema::create('downloads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('uuid')->nullable();
            $table->unsignedBigInteger('expedition_id')->index('downloads_expedition_id_foreign');
            $table->unsignedBigInteger('actor_id')->index('downloads_actor_id_foreign');
            $table->string('file', 255)->nullable();
            $table->string('type');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();

            $table->index(['expedition_id', 'actor_id', 'file']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
