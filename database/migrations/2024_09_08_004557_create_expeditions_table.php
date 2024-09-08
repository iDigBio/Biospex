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
        Schema::create('expeditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->nullable();
            $table->unsignedBigInteger('project_id')->index('expeditions_project_id_foreign');
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('keywords', 255)->nullable();
            $table->unsignedBigInteger('workflow_id')->nullable()->index('expeditions_workflow_id_foreign');
            $table->unsignedBigInteger('geo_locate_form_id')->nullable()->index('expeditions_geo_locate_form_id_foreign');
            $table->boolean('completed')->default(false);
            $table->boolean('locked')->default(false);
            $table->string('logo_file_name')->nullable();
            $table->integer('logo_file_size')->nullable();
            $table->string('logo_content_type')->nullable();
            $table->timestamp('logo_updated_at')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expeditions');
    }
};
