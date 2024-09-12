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
        /*
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
        */

        /*
        users
        groups
        projects
        expeditions
        downloads
        bingos
        bingo_maps
        events
        event_teams
        */

        /*
        $users = \App\Models\User::get(['id', 'uuid']);
        Schema::table('users', function (Blueprint $table) {
            DB::statement('ALTER TABLE `users` DROP COLUMN `uuid`;');
            DB::statement('ALTER TABLE `users` ADD `uuid` CHAR(36) NOT NULL AFTER `id`, ADD INDEX `users_uuid_index` (`uuid`);');
        });
        $users->each(function ($user) {
            $record = \App\Models\User::find($user->id);
            $oldUuid = $this->getUuid($user->uuid);
            $record->uuid = $oldUuid;
            $record->save();
        });

        $groups = \App\Models\Group::get(['id', 'uuid']);
        Schema::table('groups', function (Blueprint $table) {
            DB::statement('ALTER TABLE `groups` DROP COLUMN `uuid`;');
            DB::statement('ALTER TABLE `groups` ADD `uuid` CHAR(36) NOT NULL AFTER `id`, ADD INDEX `groups_uuid_index` (`uuid`);');
        });
        $groups->each(function ($group) {
            $group->uuid = \Str::uuid();
            $group->save();
        });

        $projects = \App\Models\Project::get(['id', 'uuid']);
        Schema::table('projects', function (Blueprint $table) {
            DB::statement('ALTER TABLE `projects` DROP COLUMN `uuid`;');
            DB::statement('ALTER TABLE `projects` ADD `uuid` CHAR(36) NOT NULL AFTER `id`, ADD INDEX `projects_uuid_index` (`uuid`);');
        });
        $projects->each(function ($project) {
            $record = \App\Models\Project::find($project->id);
            $oldUuid = $this->getUuid($project->uuid);
            $record->uuid = $oldUuid;
            $record->save();
        });

        $expeditions = \App\Models\Expedition::get(['id', 'uuid']);
        Schema::table('groups', function (Blueprint $table) {
            DB::statement('ALTER TABLE `expeditions` DROP COLUMN `uuid`;');
            DB::statement('ALTER TABLE `expeditions` ADD `uuid` CHAR(36) NOT NULL AFTER `id`, ADD INDEX `expeditions_uuid_index` (`uuid`);');
        });
        $expeditions->each(function ($expedition) {
            $record = \App\Models\Expedition::find($expedition->id);
            $oldUuid = $this->getUuid($expedition->uuid);
            $record->uuid = $oldUuid;
            $record->save();
        });

        $downloads = \App\Models\Download::get(['id', 'uuid']);
        Schema::table('downloads', function (Blueprint $table) {
            DB::statement('ALTER TABLE `downloads` DROP COLUMN `uuid`;');
            DB::statement('ALTER TABLE `downloads` ADD `uuid` CHAR(36) NOT NULL AFTER `id`, ADD INDEX `downloads_uuid_index` (`uuid`);');
        });
        $downloads->each(function ($download) {
            $record = \App\Models\Download::find($download->id);
            $oldUuid = $this->getUuid($download->uuid);
            $record->uuid = $oldUuid;
            $record->save();
        });

        $bingos = \App\Models\Bingo::get();
        $bingos->each(function ($bingo) {
            $bingo->uuid = \Str::uuid();
            $bingo->save();
        });

        $bingoMaps = \App\Models\BingoMap::get(['id', 'uuid']);
        Schema::table('bingo_maps', function (Blueprint $table) {
            DB::statement('ALTER TABLE `bingo_maps` DROP COLUMN `uuid`;');
            DB::statement('ALTER TABLE `bingo_maps` ADD `uuid` CHAR(36) NOT NULL AFTER `id`, ADD INDEX `bingo_maps_uuid_index` (`uuid`);');
        });
        $bingoMaps->each(function ($bingoMap) {
            $record = \App\Models\BingoMap::find($bingoMap->id);
            $record->uuid = \Str::uuid();
            $record->save();
        });

        $events = \App\Models\Event::get();
        $events->each(function ($event) {
            $event->uuid = \Str::uuid();
            $event->save();
        });

        $teams = \App\Models\EventTeam::get(['id', 'uuid']);
        Schema::table('event_teams', function (Blueprint $table) {
            DB::statement('ALTER TABLE `event_teams` DROP COLUMN `uuid`;');
            DB::statement('ALTER TABLE `event_teams` ADD `uuid` CHAR(36) NOT NULL AFTER `id`, ADD INDEX `event_teams_uuid_index` (`uuid`);');
        });
        $teams->each(function ($team) {
            $record = \App\Models\EventTeam::find($team->id);
            $oldUuid = $this->getUuid($team->uuid);
            $record->uuid = $oldUuid;
            $record->save();
        });
        */

        $wedigbioEvents = \App\Models\WeDigBioEventDate::get();
        Schema::table('wedigbio_event_dates', function (Blueprint $table) {
            DB::statement('ALTER TABLE `wedigbio_event_dates` ADD `uuid` CHAR(36) NOT NULL AFTER `id`, ADD INDEX `wedigbio_event_dates_uuid_index` (`uuid`);');
        });
        $wedigbioEvents->each(function ($wedigbioEvent) {
            $wedigbioEvent->uuid = \Str::uuid();
            $wedigbioEvent->save();
        });

    }

    private function getUuid($value): string
    {
        $uuid = bin2hex($value);

        return substr($uuid, 0, 8).'-'.substr($uuid, 8, 4).'-'.substr($uuid, 12, 4).'-'.substr($uuid, 16, 4).'-'.substr($uuid, 20);
    }
}
