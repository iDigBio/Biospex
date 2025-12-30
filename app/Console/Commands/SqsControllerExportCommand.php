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
 * Command to control export listeners via Supervisor.
 * Usage: php artisan update:listen-controller {action=start|stop|restart}
 */
class SqsControllerExportCommand extends Command
{
    protected $signature = 'update:listen-controller {action=start|stop|restart}';

    protected $description = 'Start/stop/restart export listeners via Supervisor (environment-neutral)';

    /**
     * Create a new command instance.
     *
     * @param  SupervisorControlService  $service  Service to control supervisor processes
     */
    public function __construct(protected SupervisorControlService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int Command exit code (SUCCESS=0 or FAILURE=1)
     */
    public function handle(): int
    {
        try {
            $this->service->control([
                config('services.aws.queues.export_update'),
            ], $this->argument('action'));

            \Log::info('Export listeners '.$this->argument('action').'ed');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
