<?php
/**
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

use App\Models\RapidRecord;
use App\Models\User;
use App\Notifications\UpdateNotification;
use App\Services\RapidExportService;
use App\Services\RapidFileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Services\RapidFileService
     */
    private $service;

    /**
     * @var \App\Services\RapidExportService
     */
    private $rapidExportService;

    /**
     * AppCommand constructor.
     */
    public function __construct(RapidFileService $service, RapidExportService $rapidExportService)
    {
        parent::__construct();
        $this->service = $service;
        $this->rapidExportService = $rapidExportService;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $fields = [
            "georeferenceVerificationStatus_rapid", "georeferenceProtocol_rapid", "georeferencedBy_rapid"
        ];
        $user = User::find(1);
        $user->notify(new UpdateNotification('Name_of_file', 500, $fields, null));
        //$this->exportTest();
        //$this->createHeaderFile();
    }

    private function exportTest()
    {
        $data = [
            "_token"            => "KF8Rvxxp8hI7k04VKcHlYvphN1nZitP9EQr1WPvD",
            "exportDestination" => "taxonomic",
            "exportType"        => "csv",
        ];

        $fields = $this->rapidExportService->mapDirectFields($data);
        $this->rapidExportService->buildExport($fields, true);
        dd($fields);

    }

    private function createHeaderFile()
    {
        $record = RapidRecord::first()->getAttributes();
        unset($record['_id'], $record['created_at'], $record['updated_at']);
        $keys = array_keys($record);
        $this->service->storeHeader($keys);
        dd($this->service->getHeader());
    }
}