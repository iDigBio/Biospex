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

use File;
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
        $this->fixTables();
        $this->addUuid();
        $this->updateUuid();
    }

    private function addUuid()
    {
        $tablesAdd = [
            'bingos' => 'App\Models\Bingo',
            'events' => 'App\Models\Event',
            'event_users' => 'App\Models\EventUser',
            'geo_locate_communities' => 'App\Models\GeoLocateCommunity',
            'geo_locate_data_sources' => 'App\Models\GeoLocateDataSource',
            'geo_locate_forms' => 'App\Models\GeoLocateForm',
            'group_invites' => 'App\Models\GroupInvite',
            'resources' => 'App\Models\Resource',
        ];

        collect($tablesAdd)->each(function ($className, $tableName) {
            $this->addUuidToTable($tableName);
            $this->createNewUuid($className);
            $this->setUuidNotNull($tableName);
        });
    }

    public function updateUuid(): void
    {
        $tablesUpdate = [
            'bingo_maps' => 'App\Models\BingoMap',
            'downloads' => 'App\Models\Download',
            'event_teams' => 'App\Models\EventTeam',
            'expeditions' => 'App\Models\Expedition',
            'groups' => 'App\Models\Group',
            'projects' => 'App\Models\Project',
            'users' => 'App\Models\User',
        ];

        collect($tablesUpdate)->each(function ($className, $tableName) {
            $this->addUuidNewToTable($tableName);
            $this->updateNewUuid($className);
            $this->dropOldUuidAndRename($tableName);
            $this->setUuidNotNull($tableName);
        });
    }

    private function getUuid($value): string
    {
        $uuid = bin2hex($value);

        return substr($uuid, 0, 8).'-'.substr($uuid, 8, 4).'-'.substr($uuid, 12, 4).'-'.substr($uuid, 16, 4).'-'.substr($uuid, 20);
    }

    public function addUuidToTable(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            DB::statement('ALTER TABLE `'.$tableName.'` ADD `uuid` CHAR(36) NULL AFTER `id`;');
        });
    }

    public function createNewUuid(string $className): void
    {
        $class = \App::make($className);
        $records = $class::get();
        $records->each(function ($record) {
            $record->uuid = \Str::uuid();
            $record->save();
        });
    }

    public function setUuidNotNull(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            DB::statement('ALTER TABLE `'.$tableName.'` CHANGE `uuid` `uuid` CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;');
            DB::statement('ALTER TABLE `'.$tableName.'` ADD UNIQUE `'.$tableName.'_uuid_unique` (`uuid`);');
        });
    }

    public function addUuidNewToTable(string $tableName): void
    {
        DB::statement('ALTER TABLE `'.$tableName.'` ADD `uuid_new` CHAR(36) NULL AFTER `uuid`;');
    }

    public function updateNewUuid(string $className): void
    {
        $class = \App::make($className);
        $records = $class::get();
        $records->each(function ($record) {
            $uuid = $this->getUuid($record->uuid);
            $record->uuid_new = $uuid;
            $record->save();
        });
    }

    public function dropOldUuidAndRename(string $tableName)
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            DB::statement('ALTER TABLE `'.$tableName.'` DROP COLUMN `uuid`, CHANGE `uuid_new` `uuid` CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;');
        });
    }

    public function fixTables()
    {
        DB::statement('TRUNCATE `migrations`;');
        DB::unprepared(File::get(storage_path('migrations.sql')));

        Schema::dropIfExists('project_old_workflow');

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

        Schema::table('invites', function (Blueprint $table) {
            DB::statement('RENAME TABLE `biospex`.`invites` TO `biospex`.`group_invites`;');
            DB::statement('ALTER TABLE `group_invites` RENAME INDEX `invites_group_id_email_index` TO `group_invites_group_id_email_index`;');
            DB::statement('ALTER TABLE `group_invites` RENAME INDEX `invites_code_index` TO `group_invites_code_index`;');
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

        Schema::table('project_resources', function (Blueprint $table) {
            DB::statement('ALTER TABLE `project_resources` ADD `download_created_at` TIMESTAMP NULL DEFAULT NULL AFTER `download_updated_at`;');
        });
    }
}
