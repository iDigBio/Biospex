<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('geo_locate_forms')) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            Schema::drop('geo_locate_forms');
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        }

        Schema::create('geo_locate_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->foreign('group_id')->references('id')
                ->on('groups')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->string('name');
            $table->string('source');
            $table->string('hash');
            $table->json('fields');
            $table->unique(['group_id', 'name', 'source', 'hash'], 'unique_group_id_name_source_hash');
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('geo_locate_forms');
        Schema::enableForeignKeyConstraints();
    }
};
