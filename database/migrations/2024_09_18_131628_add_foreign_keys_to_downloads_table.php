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
        Schema::table('downloads', function (Blueprint $table) {
            $table->foreign(['actor_id'])->references(['id'])->on('actors')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['expedition_id'])->references(['id'])->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('downloads', function (Blueprint $table) {
            $table->dropForeign('downloads_actor_id_foreign');
            $table->dropForeign('downloads_expedition_id_foreign');
        });
    }
};
