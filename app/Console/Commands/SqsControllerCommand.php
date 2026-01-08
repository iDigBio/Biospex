<?php

/*
 * Copyright (C) 2014 - 2026, Biospex
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
use Illuminate\Support\Facades\Config;

/**
 * Unified command to control any SQS listener via Supervisor.
 * Usage: php artisan sqs:control export_update start
 */
class SqsControllerCommand extends Command
{
    /**
     * The console command signature.
     */
    protected $signature = 'sqs:control {queue_keys*} {--action=start : start|stop|restart}';

    protected $description = 'Control one or more SQS listeners via Supervisor by providing config queue keys';

    public function __construct(protected SupervisorControlService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keys = $this->argument('queue_keys');
        $action = $this->option('action');

        $queueNames = [];
        foreach ($keys as $key) {
            $name = Config::get("services.aws.sqs.{$key}");
            if (empty($name)) {
                $this->warn("Invalid queue key: '{$key}' - skipping.");

                continue;
            }
            $queueNames[] = $name;
        }

        if (empty($queueNames)) {
            $this->error('No valid queue names found to control.');

            return self::FAILURE;
        }

        try {
            $this->service->control($queueNames, $action);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Supervisor Control Failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
