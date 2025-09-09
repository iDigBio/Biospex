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
        if (Schema::hasTable('bingo_words')) {
            Schema::table('bingo_words', function (Blueprint $table) {
                $table->foreign(['bingo_id'])->references(['id'])->on('bingos')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bingo_words')) {
            Schema::table('bingo_words', function (Blueprint $table) {
                $table->dropForeign('bingo_words_bingo_id_foreign');
            });
        }
    }
};
