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

use App\Services\Csv\ZooniverseCsvService;
use Illuminate\Console\Command;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Services\Csv\ZooniverseCsvService
     */
    private ZooniverseCsvService $service;

    /**
     * Create a new command instance.
     */
    public function __construct(ZooniverseCsvService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $result = $this->service->checkCsvRequest(461);
        if ($result['media'][0]['metadata']['state'] === 'creating') {
            echo 'Zooniverse csv creation for Expedition Id 461 is still in progress.';

            return;
        }

        $uri = $result['media'][0]['src'] ?? null;
        if ($uri === null) {
            dd('null');
        }

        echo $uri . PHP_EOL;


    }
}