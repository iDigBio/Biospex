<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 'expedition_id',
     * 'actor_id',
     * 'stage',
     * 'queued',
     * 'total',
     * 'error'
     */
    public function up(): void
    {
        Schema::table('export_queues', function(Blueprint $table) {
            $table->dropColumn('processed');
            $table->renameColumn('count', 'total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('export_queues', function(Blueprint $table) {
            $table->tinyInteger('processed')->default(0)->after('total');
            $table->renameColumn('total', 'count');
            $table->renameColumn('access_uri', 'url');
        });
    }
};
