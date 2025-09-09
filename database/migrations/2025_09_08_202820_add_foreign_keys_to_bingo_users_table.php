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
        if (Schema::hasTable('bingo_users')) {
            Schema::table('bingo_users', function (Blueprint $table) {
                $table->foreign(['bingo_id'], 'bingo_maps_bingo_id_foreign')->references(['id'])->on('bingos')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bingo_users')) {
            Schema::table('bingo_users', function (Blueprint $table) {
                $table->dropForeign('bingo_maps_bingo_id_foreign');
            });
        }
    }
};
