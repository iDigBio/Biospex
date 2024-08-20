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
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_id')->index('events_project_id_foreign');
            $table->unsignedBigInteger('owner_id')->index('events_owner_id_foreign');
            $table->string('title');
            $table->string('description', 255);
            $table->string('hashtag', 255)->nullable();
            $table->string('contact');
            $table->string('contact_email');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('timezone');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
