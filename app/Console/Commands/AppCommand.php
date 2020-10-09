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

use App\Jobs\ZooniverseCsvJob;
use App\Repositories\Interfaces\Expedition;
use App\Services\Csv\ZooniverseCsvService;
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
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $contract;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $service;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * AppCommand constructor.
     *
     * @param \App\Services\Csv\ZooniverseCsvService $service
     */
    public function __construct(ZooniverseCsvService $service, Expedition $expeditionContract) {
        parent::__construct();
        $this->service = $service;
        $this->expeditionContract = $expeditionContract;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        //$expedition = $this->expeditionContract->getExpeditionForZooniverseProcess(254);
        //$result = $this->checkForRequiredVariables($expedition);
        //dd($result);

        ZooniverseCsvJob::dispatch([254], true);
    }

    public function checkForRequiredVariables($expedition)
    {
        return null === $expedition ||
            ! isset($expedition->panoptesProject) ||
            null === $expedition->panoptesProject->panoptes_workflow_id ||
            null === $expedition->panoptesProject->panoptes_project_id;
    }
}