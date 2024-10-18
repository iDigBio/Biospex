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

use App\Jobs\ZooniverseCsvDownloadJob;
use App\Services\Csv\ZooniverseCsvService;
use Illuminate\Console\Command;

/**
 * Class ZooniverseDownloadCommand
 */
class ZooniverseDownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zooniverse:download {expeditionId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends request for Zooniverse csv download if ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(ZooniverseCsvService $service)
    {
        try {
            $expeditionId = $this->argument('expeditionId');

            $result = $service->checkCsvRequest($expeditionId);

            if ($result['media'][0]['metadata']['state'] === 'creating') {
                echo t('CSV returned state of creating.').PHP_EOL;

                return;
            }

            if ($result['media'][0]['metadata']['state'] === 'ready' && ! isset($result['media'][0]['src'])) {
                throw new \Exception(t('Uri is not available at this time.'));
            }

            ZooniverseCsvDownloadJob::dispatch($expeditionId, $result['media'][0]['src']);
        } catch (\Throwable $throwable) {
            echo $throwable->getMessage().PHP_EOL;
        }
    }
}
