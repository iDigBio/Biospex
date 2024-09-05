<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->binary('uuid')->nullable();
            $table->unsignedBigInteger('expedition_id')->index('downloads_expedition_id_foreign');
            $table->unsignedBigInteger('actor_id')->index('downloads_actor_id_foreign');
            $table->string('file', 255)->nullable();
            $table->string('type');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->index(['expedition_id', 'actor_id', 'file']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('downloads');
    }
}
