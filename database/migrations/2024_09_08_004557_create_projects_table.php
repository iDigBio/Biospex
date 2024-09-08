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
        Schema::create('projects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->binary('uuid')->nullable();
            $table->unsignedBigInteger('group_id')->index();
            $table->string('title', 255)->nullable();
            $table->string('slug', 255)->nullable()->index();
            $table->string('contact', 255)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_title', 255)->nullable();
            $table->string('organization_website', 255)->nullable();
            $table->string('organization', 255)->nullable();
            $table->text('project_partners')->nullable();
            $table->text('funding_source')->nullable();
            $table->string('description_short', 255)->nullable();
            $table->text('description_long')->nullable();
            $table->text('incentives')->nullable();
            $table->string('geographic_scope', 255)->nullable();
            $table->string('taxonomic_scope', 255)->nullable();
            $table->string('temporal_scope', 255)->nullable();
            $table->string('keywords', 255)->nullable();
            $table->string('blog_url', 255)->nullable();
            $table->string('facebook', 255)->nullable();
            $table->string('twitter', 255)->nullable();
            $table->string('activities', 255)->nullable();
            $table->string('language_skills', 255)->nullable();
            $table->string('logo_file_name', 255)->nullable();
            $table->integer('logo_file_size')->nullable();
            $table->string('logo_content_type', 255)->nullable();
            $table->timestamp('logo_updated_at')->nullable();
            $table->string('banner_file')->nullable();
            $table->text('target_fields')->nullable();
            $table->binary('advertise')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
