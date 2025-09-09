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
        if (! Schema::hasTable('bingo_users')) {
            Schema::create('bingo_users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('bingo_id')->index('bingo_maps_bingo_id_foreign');
                $table->char('uuid', 36)->unique();
                $table->string('ip', 30);
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->string('city', 100);
                $table->boolean('winner')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bingo_users');
    }
};
