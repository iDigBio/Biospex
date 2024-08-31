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

use App\Events\WeDigBioProgressEvent;
use App\Services\Models\WeDigBioEventDateModelService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WeDigBioEventProgressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private int $dateId;

    /**
     * Create a new job instance.
     *
     * @param int $dateId
     */
    public function __construct(int $dateId)
    {
        $this->dateId = $dateId;
        $this->onQueue(config('config.queue.event'));
    }

    /**
     * Handle Job.
     *
     * @param \App\Services\Models\WeDigBioEventDateModelService $weDigBioEventDateModelService
     * @return void
     */
    public function handle(WeDigBioEventDateModelService $weDigBioEventDateModelService)
    {
        $weDigBioDate = $weDigBioEventDateModelService->getWeDigBioEventTranscriptions($this->dateId);
        $id = $weDigBioDate->active ? 0 : $weDigBioDate->id;

        $data = [$id => \View::make('common.wedigbio-progress-content', compact('weDigBioDate'))->render()];

        WeDigBioProgressEvent::dispatch($id, $data);
    }
}
