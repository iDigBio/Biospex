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

use App\Jobs\ExportQueueJob;
use App\Services\Model\ExportQueueService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ExportQueueCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fire export queue job.';

    /**
     * @var \App\Services\Model\ExportQueueService
     */
    public $exportQueueService;


    /**
     * ExportQueueCommand constructor.
     * @param \App\Services\Model\ExportQueueService $exportQueueService
     */
    public function __construct(ExportQueueService $exportQueueService)
    {
        parent::__construct();
        $this->exportQueueService = $exportQueueService;
    }

    /**
     * Handle job.
     */
    public function handle()
    {
        $record = $this->exportQueueService->findBy('error', 0);

        if ($record === null)
        {
            return;
        }

        if ($record->queued)
        {
            ExportQueueJob::dispatch($record);

            return;
        }

        if (! $record->queued)
        {
            $this->exportQueueService->update(['queued' => 1], $record->id);
            event('exportQueue.updated');
        }
    }
}