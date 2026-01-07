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
        Schema::table('project_assets', function (Blueprint $table) {
            $table->dropColumn([
                'download_file_name',
                'download_file_size',
                'download_content_type',
                'download_updated_at',
                'download_created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_assets', function (Blueprint $table) {
            $table->string('download_file_name')->nullable();
            $table->integer('download_file_size')->nullable();
            $table->string('download_content_type')->nullable();
            $table->timestamp('download_updated_at')->nullable();
            $table->timestamp('download_created_at')->nullable();
        });
    }
};
