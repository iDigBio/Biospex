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

use App\Services\SupervisorControlService;
use Illuminate\Console\Command;

/**
 * Command to control the batch update listener via Supervisor.
 * Provides functionality to start, stop, or restart the batch update process.
 */
class SqsBatchControllerCommand extends Command
{
    protected $signature = 'batch:listen-controller {action=start|stop|restart}';

    protected $description = 'Start/stop/restart the batch update listener via Supervisor (environment-neutral)';

    /**
     * {@inheritDoc}
     *
     * @param  SupervisorControlService  $service  Service to control supervisor processes
     */
    public function __construct(protected SupervisorControlService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command to control batch listener.
     *
     * @return int Command exit code
     *
     * @throws \Throwable When supervisor control operation fails
     */
    public function handle(): int
    {
        try {
            $this->service->control([
                'update' => 'listener-batch-update',
            ], $this->argument('action'));

            $this->info('Batch listener '.$this->argument('action').'ed');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to control batch listener: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
