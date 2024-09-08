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
        Schema::table('event_transcriptions', function (Blueprint $table) {
            $table->foreign(['event_id'])->references(['id'])->on('events')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['team_id'])->references(['id'])->on('event_teams')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('event_users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_transcriptions', function (Blueprint $table) {
            $table->dropForeign('event_transcriptions_event_id_foreign');
            $table->dropForeign('event_transcriptions_team_id_foreign');
            $table->dropForeign('event_transcriptions_user_id_foreign');
        });
    }
};
