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
        if (! Schema::hasTable('geo_locate_forms')) {
            Schema::create('geo_locate_forms', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->char('uuid', 36)->unique();
                $table->unsignedBigInteger('group_id');
                $table->string('name');
                $table->string('hash');
                $table->json('fields');
                $table->timestamps();

                $table->unique(['group_id', 'name', 'hash'], 'unique_group_id_name_source_hash');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geo_locate_forms');
    }
};
