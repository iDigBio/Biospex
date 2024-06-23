<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpeditionStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expedition_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expedition_id')->index('expedition_stats_expedition_id_foreign');
            $table->integer('local_subject_count')->default(0);
            $table->integer('subject_count')->default(0);
            $table->integer('transcriptions_total')->default(0);
            $table->integer('transcriptions_completed')->default(0)->index();
            $table->decimal('percent_completed', 5)->default(0.00);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expedition_stats');
    }
}
