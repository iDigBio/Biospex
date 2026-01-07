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
        Schema::table('expeditions', function (Blueprint $table) {
            $table->dropColumn([
                'logo_file_name',
                'logo_file_size',
                'logo_content_type',
                'logo_updated_at',
                'logo_created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expeditions', function (Blueprint $table) {
            $table->string('logo_file_name')->nullable();
            $table->integer('logo_file_size')->nullable();
            $table->string('logo_content_type')->nullable();
            $table->timestamp('logo_updated_at')->nullable();
            $table->timestamp('logo_created_at')->nullable();
        });
    }
};
