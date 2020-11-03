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
use App\Repositories\Interfaces\ExportQueue;
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
     * @var ExportQueue
     */
    public $exportQueueContract;


    /**
     * ExportQueueCommand constructor.
     * @param ExportQueue $exportQueueContract
     */
    public function __construct(ExportQueue $exportQueueContract)
    {
        parent::__construct();
        $this->exportQueueContract = $exportQueueContract;
    }

    /**
     * Handle job.
     */
    public function handle()
    {
        $record = $this->exportQueueContract->getFirstExportWithoutError();

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
            $this->exportQueueContract->update(['queued' => 1], $record->id);
            event('exportQueue.updated');
        }
    }
}