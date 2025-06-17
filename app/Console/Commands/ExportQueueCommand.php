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

use App\Services\Actor\Zooniverse\ZooniverseExportQueue;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class ExportQueueCommand
 */
class ExportQueueCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:queue {expeditionId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fire export queue process. Expedition Id resets the Expedition.';

    /**
     * ExportQueueCommand constructor.
     */
    public function __construct(protected ZooniverseExportQueue $zooniverseExportQueue)
    {
        parent::__construct();
    }

    /**
     * Handle job.
     */
    public function handle(): void
    {
        is_null($this->argument('expeditionId')) ?
            $this->zooniverseExportQueue->processQueue() :
            $this->zooniverseExportQueue->resetExpeditionExport($this->argument('expeditionId'));
    }
}
