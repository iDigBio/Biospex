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
        if (! Schema::hasTable('event_transcriptions')) {
            Schema::create('event_transcriptions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('classification_id');
                $table->unsignedBigInteger('event_id')->index('event_transcriptions_event_id_foreign');
                $table->unsignedBigInteger('team_id')->index('event_transcriptions_team_id_foreign');
                $table->unsignedBigInteger('user_id')->index('event_transcriptions_user_id_foreign');
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_transcriptions');
    }
};
