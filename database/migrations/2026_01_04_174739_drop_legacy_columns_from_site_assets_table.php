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
        Schema::table('site_assets', function (Blueprint $table) {
            $table->dropColumn([
                'document_file_name',
                'document_file_size',
                'document_content_type',
                'document_updated_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_assets', function (Blueprint $table) {
            $table->string('document_file_name')->nullable();
            $table->integer('document_file_size')->nullable();
            $table->string('document_content_type')->nullable();
            $table->timestamp('document_updated_at')->nullable();
        });
    }
};
