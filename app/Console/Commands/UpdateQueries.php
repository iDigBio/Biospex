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
    protected $signature = 'update:queries';

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
        $this->addUuid();
        $this->updateUuid();

        \Artisan::call('zooniverse:count --update');
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
            'wedigbio_events' => 'App\Models\WeDigBioEvent',
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
            'bingo_users' => 'App\Models\BingoUser',
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
        \Artisan::call('lada-cache:flush');
        $class = \App::make($className);
        $records = $class::all();
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
        \Artisan::call('lada-cache:flush');
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
}
