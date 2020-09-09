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
use App\Repositories\Interfaces\RapidHeader;
use App\Repositories\Interfaces\RapidUpdate;
use App\Services\CsvService;
use Illuminate\Console\Command;

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
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * @var \App\Repositories\Interfaces\RapidHeader
     */
    private $rapidHeader;

    /**
     * @var \App\Models\RapidUpdate
     */
    private $rapidUpdate;

    /**
     * AppCommand constructor.
     */
    public function __construct(CsvService $csvService, RapidHeader $rapidHeader, RapidUpdate $rapidUpdate)
    {
        parent::__construct();
        $this->csvService = $csvService;
        $this->rapidHeader = $rapidHeader;
        $this->rapidUpdate = $rapidUpdate;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $fields = $this->fields();
        $results = collect($fields)->forget('_token')->mapToGroups(function($array, $index){
            foreach ($array as $index => $value) {
                return
                dd($value);
            }
        });

        //$this->export();
        //$this->checkHeader();
    }

    public function export()
    {
        $records = RapidRecord::limit(10)->get();
        $first = $records->first();

        $header = array_keys($first->toArray());

        $file = \Storage::path('export-test.csv');
        $this->csvService->writerCreateFromPath($file);
        $this->csvService->insertOne($header);
        $this->csvService->insertAll($records->toArray());
    }

    public function checkHeader()
    {
        $rapidheader = $this->rapidHeader->first();
        dd($rapidheader->header);
    }

    public function fields()
    {
        return [
            "_token"      => "PRRiJSRfTTZmRnGK1ZTsglByT8PwhwLWeGOuRLfT",
            "exportField" => [
                "catalogNumber",
                "continent",
                null,
                null,
            ],
            "_gbifR"      => [
                "gbifID_gbifR",
                "acceptedNameUsageID_gbifR",
                null,
                null,
            ],
            "_idbP"       => [
                "idigbio_uuid_idbP",
                "bed_idbP",
                null,
                null,
            ],
            "_gbifP"      => [
                "abstract_gbifP",
                "acceptedScientificName_gbifP",
                null,
                null,
            ],
            "_idbR"       => [
                "acceptedNameUsage_idbR",
                "associatedMedia_idbR",
                null,
                null,
            ],
            "_rapid"      => [
                "country_rapid",
                "countryCode_rapid",
                null,
                null,
            ]
        ];
    }
}