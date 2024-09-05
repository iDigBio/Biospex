<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // DB::raw("ALTER TABLE `bingo_maps` CHANGE `ip` `ip` VARCHAR(30) NOT NULL;");
        Schema::table('bingo_maps', function (Blueprint $table) {
            $table->string('ip', 30)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DB::raw("ALTER TABLE `bingo_maps` CHANGE `ip` `ip` VARCHAR(15) NOT NULL;");
        Schema::table('bingo_maps', function (Blueprint $table) {
            $table->binary('ip')->change();
        });
    }
};
