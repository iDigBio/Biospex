<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('event_groups')) {
            Schema::create('event_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('event_id');
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->string('title');
                $table->timestamps();

                $table->unique(['event_id', 'title'], 'event_group_title');
            });

            DB::statement("ALTER TABLE event_groups ADD uuid BINARY(16) NULL AFTER id");
            DB::statement('CREATE UNIQUE INDEX uuid_unique ON event_groups (uuid);');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_groups');
    }
}
