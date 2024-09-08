<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class UpdateQueries
 */
class UpdateQueries extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'update:queries {method?}';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire command
     */
    public function handle()
    {
        Schema::table('actor_contacts', function (Blueprint $table) {
            DB::statement('ALTER TABLE `actor_contacts` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('actors', function (Blueprint $table) {
            DB::statement('ALTER TABLE `actors` CHANGE `title` `title` VARCHAR(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `url` `url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `class` `class` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('downloads', function (Blueprint $table) {
            DB::statement('ALTER TABLE `downloads` CHANGE `uuid` `uuid` BINARY(16) NOT NULL, CHANGE `file` `file` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('event_users', function (Blueprint $table) {
            DB::statement('ALTER TABLE `event_users` CHANGE `nfn_user` `nfn_user` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('events', function (Blueprint $table) {
            DB::statement('ALTER TABLE `events` CHANGE `title` `title` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `contact` `contact` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `contact_email` `contact_email` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `start_date` `start_date` TIMESTAMP NOT NULL, CHANGE `end_date` `end_date` TIMESTAMP NOT NULL, CHANGE `timezone` `timezone` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('expeditions', function (Blueprint $table) {
            DB::statement('ALTER TABLE `expeditions` CHANGE `uuid` `uuid` BINARY(16) NOT NULL, CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `description` `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `keywords` `keywords` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ');
        });

        Schema::table('export_queue_files', function (Blueprint $table) {
            DB::statement('ALTER TABLE `export_queue_files` CHANGE `subject_id` `subject_id` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `access_uri` `access_uri` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('faq_categories', function (Blueprint $table) {
            DB::statement('ALTER TABLE `faq_categories` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ');
        });

        Schema::table('faqs', function (Blueprint $table) {
            DB::statement('ALTER TABLE `faqs` CHANGE `question` `question` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `answer` `answer` VARCHAR(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL; ');
        });

        Schema::table('groups', function (Blueprint $table) {
            DB::statement('ALTER TABLE `groups` CHANGE `uuid` `uuid` BINARY(16) NOT NULL, CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('notices', function (Blueprint $table) {
            DB::statement('ALTER TABLE `notices` CHANGE `message` `message` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('ocr_queue_files', function (Blueprint $table) {
            DB::statement('ALTER TABLE `ocr_queue_files` CHANGE `subject_id` `subject_id` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `access_uri` `access_uri` VARCHAR(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('profiles', function (Blueprint $table) {
            DB::statement('UPDATE profiles set timezone = "America/New_York" where timezone IS NULL;');
            DB::statement('ALTER TABLE `profiles` CHANGE `timezone` `timezone` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
            DB::statement('ALTER TABLE `profiles` CHANGE `first_name` `first_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `last_name` `last_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('resources', function (Blueprint $table) {
            DB::statement('ALTER TABLE `resources` CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `description` `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('team_categories', function (Blueprint $table) {
            DB::statement('ALTER TABLE `team_categories` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('teams', function (Blueprint $table) {
            DB::statement('ALTER TABLE `teams` CHANGE `first_name` `first_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `last_name` `last_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('users', function (Blueprint $table) {
            DB::statement('ALTER TABLE `users` CHANGE `uuid` `uuid` BINARY(16) NOT NULL, CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL, CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('wedigbio_projects', function (Blueprint $table) {
            DB::statement('ALTER TABLE `wedigbio_projects` CHANGE `panoptes_project_id` `panoptes_project_id` INT NOT NULL;');
        });

        Schema::table('workflows', function (Blueprint $table) {
            DB::statement('ALTER TABLE `workflows` CHANGE `title` `title` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
        });

        Schema::table('profiles', function (Blueprint $table) {
            DB::statement('ALTER TABLE `profiles` ADD `avatar_created_at` TIMESTAMP NULL DEFAULT NULL AFTER `avatar_updated_at`;');
        });

        Schema::table('projects', function (Blueprint $table) {
            DB::statement('ALTER TABLE `projects` ADD `logo_created_at` TIMESTAMP NULL DEFAULT NULL AFTER `logo_updated_at`;');
        });

        Schema::table('expeditions', function (Blueprint $table) {
            DB::statement('ALTER TABLE `expeditions` ADD `logo_created_at` TIMESTAMP NULL DEFAULT NULL AFTER `logo_updated_at`;');
        });

        $path = storage_path('app/migrations.sql');
        DB::unprepared(file_get_contents($path));
    }
}
