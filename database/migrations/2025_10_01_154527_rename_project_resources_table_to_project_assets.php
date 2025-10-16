<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('project_assets')) {
            Schema::rename('project_resources', 'project_assets');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('project_resources')) {
            Schema::rename('project_assets', 'project_resources');
        }
    }
};
