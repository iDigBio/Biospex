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

use App\Repositories\ExportQueueFileRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class LambdaUpdateExportQueueFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private array $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->onQueue(config('config.lambda_tube'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ExportQueueFileRepository $exportQueueFileRepository)
    {
        $attributes = [
            'error_message' => $this->data['error_message'],
            'completed' => 1,
        ];

        \Log::alert(json_encode($attributes));

        $exportQueueFileRepository->updateMany($attributes, 'subject_id', $this->data['subject_id']);
    }
}
