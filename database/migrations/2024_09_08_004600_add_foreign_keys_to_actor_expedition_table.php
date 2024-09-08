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
        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->foreign(['actor_id'], 'expedition_actor_actor_id_foreign')->references(['id'])->on('actors')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['expedition_id'], 'expedition_actor_expedition_id_foreign')->references(['id'])->on('expeditions')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actor_expedition', function (Blueprint $table) {
            $table->dropForeign('expedition_actor_actor_id_foreign');
            $table->dropForeign('expedition_actor_expedition_id_foreign');
        });
    }
};
