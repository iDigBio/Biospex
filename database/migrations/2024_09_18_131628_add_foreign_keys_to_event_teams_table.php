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
        Schema::table('event_teams', function (Blueprint $table) {
            $table->foreign(['event_id'])->references(['id'])->on('events')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_teams', function (Blueprint $table) {
            $table->dropForeign('event_teams_event_id_foreign');
        });
    }
};
