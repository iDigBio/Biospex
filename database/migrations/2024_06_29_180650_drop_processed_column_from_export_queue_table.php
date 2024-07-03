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
        Schema::table('export_queues', function (Blueprint $table) {
            $table->dropColumn('processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('export_queues', function (Blueprint $table) {
            $table->boolean('processed')->default(false)->after('count');
        });
    }
};
