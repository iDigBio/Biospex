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

use App\Repositories\ExpeditionRepository;
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
    protected $signature = 'export:queue {expeditionId?} {--R|retry}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fire export queue process. Expedition Id resets the Expedition.';

    /**
     * @var \App\Services\Download\DownloadType
     */
    private DownloadType $downloadType;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepository;

    /**
     * ExportQueueCommand constructor.
     *
     * @param \App\Services\Download\DownloadType $downloadType
     * @param \App\Repositories\ExpeditionRepository $expeditionRepository
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     */
    public function __construct(
        DownloadType $downloadType,
        ExpeditionRepository $expeditionRepository,
        ExportQueueRepository $exportQueueRepository
    ) {
        parent::__construct();
        $this->downloadType = $downloadType;
        $this->expeditionRepository = $expeditionRepository;
        $this->exportQueueRepository = $exportQueueRepository;
    }

    /**
     * Handle job.
     */
    public function handle()
    {
        is_null($this->argument('expeditionId')) ?
            $this->handleExportQueue() :
            $this->handleExpeditionReset();
    }

    /**
     * Handles starting the export queue.
     *
     * @return void
     */
    private function handleExportQueue()
    {
        $this->option('retry') ? event('exportQueue.retry') : event('exportQueue.check');
    }

    /**
     * Handles resetting Expedition attributes from command line.
     *
     * @return void
     */
    private function handleExpeditionReset()
    {
        $expeditionId = $this->argument('expeditionId');
        $expedition = $this->expeditionRepository->findWith($expeditionId, ['nfnActor', 'stat']);

        $exportQueue = $this->exportQueueRepository->findBy('expedition_id', $expeditionId);
        if (!is_null($exportQueue)) $exportQueue->delete();

        $this->downloadType->resetExpeditionData($expedition);
    }
}