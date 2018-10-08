<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('project_id');
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
                $table->unsignedInteger('owner_id');
                $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
                $table->string('title');
                $table->string('description', 104);
                $table->string('contact');
                $table->string('contact_email');
                $table->timestamp('start_date')->nullable();
                $table->timestamp('end_date')->nullable();
                $table->string('timezone')->default('America/New_York');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
