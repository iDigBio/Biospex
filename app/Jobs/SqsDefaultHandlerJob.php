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

use Illuminate\Contracts\Queue\Job as LaravelJob;

class SqsDefaultHandlerJob
{
    protected $data;

    /**
     * Construct
     * Handle defaults by sending email.
     *
     * @return void
     */
    public function _construct()
    {
    }

    /**
     * @param LaravelJob $job
     * @param array $data
     */
    public function handle(LaravelJob $job, array $data)
    {
        // This is incoming JSON payload, already decoded to an array
        var_dump($data);

        // Raw JSON payload from SQS, if necessary
        var_dump($job->getRawBody());
    }
}
