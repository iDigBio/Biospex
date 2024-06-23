<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpeditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();
            $table->binary('uuid')->nullable();
            $table->unsignedBigInteger('project_id')->index('expeditions_project_id_foreign');
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('keywords', 255)->nullable();
            $table->string('logo_file_name')->nullable();
            $table->integer('logo_file_size')->nullable();
            $table->string('logo_content_type')->nullable();
            $table->timestamp('logo_updated_at')->nullable();
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
        Schema::dropIfExists('expeditions');
    }
}
