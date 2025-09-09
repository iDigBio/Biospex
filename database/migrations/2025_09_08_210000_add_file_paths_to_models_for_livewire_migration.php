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
        // Add logo_path to projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('logo_updated_at');
        });

        // Add logo_path to expeditions table
        Schema::table('expeditions', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('logo_updated_at');
        });

        // Add avatar_path to profiles table
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('avatar_updated_at');
        });

        // Add download_path to project_resources table
        Schema::table('project_resources', function (Blueprint $table) {
            $table->string('download_path')->nullable()->after('download_updated_at');
        });

        // Add download_path to resources table
        Schema::table('resources', function (Blueprint $table) {
            $table->string('download_path')->nullable()->after('document_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove logo_path from projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });

        // Remove logo_path from expeditions table
        Schema::table('expeditions', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });

        // Remove avatar_path from profiles table
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('avatar_path');
        });

        // Remove download_path from project_resources table
        Schema::table('project_resources', function (Blueprint $table) {
            $table->dropColumn('download_path');
        });

        // Remove download_path from resources table
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('download_path');
        });
    }
};
