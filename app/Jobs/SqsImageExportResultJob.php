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

namespace App\Jobs;

use App\Services\Process\SqsImageExportProcess;
use Illuminate\Contracts\Queue\Job as LaravelJob;
use Illuminate\Foundation\Bus\Dispatchable;

class SqsImageExportResultJob
{
    use Dispatchable;

    /**
     * @var array
     */
    protected array $data;

    /**
     * @var \App\Services\Process\SqsImageExportProcess
     */
    private SqsImageExportProcess $sqsImageExportProcess;

    /**
     * Construct.
     *
     * @param \App\Services\Process\SqsImageExportProcess $sqsImageExportProcess
     */
    public function __construct(
        SqsImageExportProcess $sqsImageExportProcess
    )
    {
        $this->sqsImageExportProcess = $sqsImageExportProcess;
    }

    /**
     * Handle job from result queue.
     *
     * @param LaravelJob $job
     * @param array $data
     */
    public function handle(LaravelJob $job, array $data = [])
    {
        if (is_null($data)) {
            $job->delete();

            return;
        }

        $this->sqsImageExportProcess->process($data);

        \Artisan::call('export:poll');

        $job->delete();
    }
}