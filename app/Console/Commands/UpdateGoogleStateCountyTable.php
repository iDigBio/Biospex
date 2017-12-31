<?php

namespace App\Console\Commands;

use App\Interfaces\State;
use App\Services\Csv\Csv;
use App\Services\Requests\HttpRequest;
use Illuminate\Console\Command;

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
     * @param HttpRequest $httpRequest
     * @param Csv $csv
     * @param State $stateCountyContract
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(HttpRequest $httpRequest, Csv $csv, State $stateCountyContract)
    {
        $uri = 'https://fusiontables.google.com/exporttable?query=select+*+from+1xdysxZ94uUFIit9eXmnw1fYc6VcQiXhceFd_CVKa&o=csv';

        $filePath = storage_path('United States Counties.csv');

        $httpRequest->setHttpProvider();
        $httpRequest->getHttpClient()->request('GET', $uri, ['sink' => $filePath]);

        $csv->readerCreateFromPath(storage_path('United States Counties.csv'));
        $csv->setDelimiter();
        $csv->setEnclosure();
        $csv->setEscape();
        $rows = $csv->fetch();

        $stateCountyContract->truncateTable();

        foreach ($rows as $row)
        {
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
            $stateCountyContract->create($attributes);
        }
    }
}
