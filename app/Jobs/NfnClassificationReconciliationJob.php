<?php
/**
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

use App\Jobs\Traits\SkipNfn;
use App\Services\Process\ReconcileProcessService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NfnClassificationReconciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var int
     */
    public $expeditionId;

    /**
     * @var bool
     */
    private $command;

    /**
     * NfnClassificationReconciliationJob constructor.
     *
     * @param int $expeditionId
     * @param bool $command
     */
    public function __construct(int $expeditionId, $command = false)
    {
        $this->expeditionId = $expeditionId;
        $this->command = $command;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Handle the job.
     *
     * @param \App\Services\Process\ReconcileProcessService $service
     */
    public function handle(ReconcileProcessService $service)
    {
        if ($this->skip($this->expeditionId)) {
            $this->delete();

            return;
        }

        $service->process($this->expeditionId);

        if ($this->command) {
            $this->delete();

            return;
        }

        NfnClassificationTranscriptJob::dispatch($this->expeditionId);
    }
}