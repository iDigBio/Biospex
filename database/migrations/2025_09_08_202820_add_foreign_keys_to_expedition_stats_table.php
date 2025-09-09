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
        if (Schema::hasTable('expedition_stats')) {
            Schema::table('expedition_stats', function (Blueprint $table) {
                $table->foreign(['expedition_id'])->references(['id'])->on('expeditions')->onUpdate('no action')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expedition_stats')) {
            Schema::table('expedition_stats', function (Blueprint $table) {
                $table->dropForeign('expedition_stats_expedition_id_foreign');
            });
        }
    }
};
