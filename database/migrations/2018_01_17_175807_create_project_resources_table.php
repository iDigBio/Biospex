<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->enum('type', ['website url', 'video url', 'file download'])->default('website url');
            $table->string('name');
            $table->string('description');
            $table->string('upload_file_name')->nullable();
            $table->integer('upload_file_size')->nullable();
            $table->string('upload_content_type')->nullable();
            $table->timestamp('upload_updated_at')->nullable();
            $table->timestamps();
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
