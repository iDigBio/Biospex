<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bingo_maps', function (Blueprint $table) {
            $table->rename('bingo_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bingo_users', function (Blueprint $table) {
            $table->rename('bingo_maps');
        });
    }
};
