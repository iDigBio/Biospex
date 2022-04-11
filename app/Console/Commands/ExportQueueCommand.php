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

use App\Repositories\ExportQueueRepository;
use App\Services\Download\DownloadType;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class ExportQueueCommand
 *
 * @package App\Console\Commands
 */
class ExportQueueCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:queue {expeditionId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fire export queue job.';

    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    public $exportQueueRepo;

    /**
     * @var \App\Services\Download\DownloadType
     */
    private $downloadType;

    /**
     * ExportQueueCommand constructor.
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepo
     * @param \App\Services\Download\DownloadType $downloadType
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepo,
        DownloadType $downloadType
    )
    {
        parent::__construct();
        $this->exportQueueRepo = $exportQueueRepo;
        $this->downloadType = $downloadType;
    }

    /**
     * Handle job.
     */
    public function handle()
    {
        $expeditionId = $this->argument('expeditionId');
        $record = $this->exportQueueRepo->findWithExpeditionNfnActor($expeditionId);

        if ($record === null)
        {
            return;
        }

        $this->downloadType->resetExpeditionData($record->expedition);
    }
}