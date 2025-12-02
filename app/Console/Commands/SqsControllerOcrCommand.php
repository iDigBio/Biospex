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
 * Command to control OCR listeners via Supervisor
 *
 * @author Biospex <biospex@gmail.com>
 */
class SqsControllerOcrCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'ocr:listen-controller {action=start|stop|restart}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start/stop/restart OCR listeners via Supervisor (environment-neutral)';

    /**
     * Create a new command instance.
     */
    public function __construct(protected SupervisorControlService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->service->control([
                config('services.aws.queues.ocr_update'),
            ], $this->argument('action'));

            $this->info('OCR listeners '.$this->argument('action').'ed');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to control OCR listeners: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
