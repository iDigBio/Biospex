<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index('project_resources_project_id_foreign');
            $table->string('type');
            $table->string('name', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('download_file_name', 255)->nullable();
            $table->integer('download_file_size')->nullable();
            $table->string('download_content_type', 255)->nullable();
            $table->timestamp('download_updated_at')->nullable();
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
        Schema::dropIfExists('project_resources');
    }
}
