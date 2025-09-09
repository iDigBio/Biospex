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
        if (! Schema::hasTable('expedition_stats')) {
            Schema::create('expedition_stats', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('expedition_id')->index('expedition_stats_expedition_id_foreign');
                $table->integer('local_subject_count')->default(0);
                $table->integer('subject_count')->default(0);
                $table->integer('transcriptions_goal')->default(0);
                $table->integer('local_transcriptions_completed')->default(0);
                $table->integer('transcriptions_completed')->default(0)->index();
                $table->integer('transcriber_count')->default(0);
                $table->integer('percent_completed')->default(0);
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
        Schema::dropIfExists('expedition_stats');
    }
};
