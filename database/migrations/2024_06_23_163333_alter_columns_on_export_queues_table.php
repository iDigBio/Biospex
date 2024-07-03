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
        Schema::table('export_queue_files', function(Blueprint $table) {
            $table->dropColumn('completed');
            $table->tinyInteger('tries')->default(0)->after('error_message');
            $table->boolean('processed')->default(false)->after('error_message');
            $table->renameColumn('count', 'total');
            $table->renameColumn('url', 'access_uri');
            $table->renameColumn('error_message', 'message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('export_queue_files', function(Blueprint $table) {
            $table->tinyInteger('completed')->default(0)->after('message');
            $table->renameColumn('access_uri', 'url');
            $table->renameColumn('message', 'error_message');
            $table->dropColumn('processed');
            $table->dropColumn('tries');
        });
    }
};
