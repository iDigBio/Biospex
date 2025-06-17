<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Services\Csv\Csv;
use App\Services\Requests\HttpRequest;
use App\Services\Transcriptions\StateCountyService;
use Illuminate\Console\Command;
use Storage;

/**
 * Class UpdateGoogleStateCountyTable
 */
class UpdateGoogleStateCountyTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:uscounties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Csv\Exception
     */
    public function handle(HttpRequest $httpRequest, Csv $csv, StateCountyService $stateCountyService)
    {
        $uri = 'https://fusiontables.google.com/exporttable?query=select+*+from+1xdysxZ94uUFIit9eXmnw1fYc6VcQiXhceFd_CVKa&o=csv';

        $filePath = Storage::path('United States Counties.csv');

        $httpRequest->setHttpProvider();
        $httpRequest->getHttpClient()->request('GET', $uri, ['sink' => $filePath]);

        $csv->readerCreateFromPath(Storage::path('United States Counties.csv'));
        $csv->setDelimiter();
        $csv->setEnclosure();
        $csv->setEscape();
        $rows = $csv->getRecords();

        $stateCountyService->truncate();

        foreach ($rows as $row) {
            $attributes = [
                'county_name' => $row[0],
                'state_county' => $row[1],
                'state_abbr' => $row[2],
                'state_abbr_cap' => $row[3],
                'geometry' => $row[4],
                'value' => $row[5],
                'geo_id' => $row[6],
                'geo_id_2' => $row[7],
                'geographic_name' => $row[8],
                'state_num' => $row[9],
                'county_num' => $row[10],
                'fips_forumla' => $row[11],
                'has_error' => $row[12],
            ];
            $stateCountyService->create($attributes);
        }
    }
}
