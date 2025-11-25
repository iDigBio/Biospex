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
 * Command to control reconciliation listener via Supervisor.
 *
 * This command allows starting, stopping, or restarting the reconciliation
 * update listener through Supervisor in an environment-neutral way.
 */
class SqsControllerReconcileCommand extends Command
{
    protected $signature = 'reconcile:listen-controller {action=start|stop|restart}';

    protected $description = 'Start/stop/restart the reconciliation update listener via Supervisor (environment-neutral)';

    /**
     * Create a new command instance.
     *
     * @param  SupervisorControlService  $service  Service to control Supervisor processes
     */
    public function __construct(protected SupervisorControlService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * Controls the reconciliation listener process through Supervisor based on the provided action.
     *
     * @return int Command exit code (SUCCESS=0 or FAILURE=1)
     */
    public function handle(): int
    {
        try {
            $this->service->control([
                config('services.aws.queues.reconcile_update'),
            ], $this->argument('action'));

            $this->info('Reconciliation listener '.$this->argument('action').'ed');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to control reconciliation listener: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
