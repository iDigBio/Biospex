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

namespace App\Console\Commands;

use App\Events\PollExportEvent;
use App\Facades\GeneralHelper;
use App\Repositories\Interfaces\ExportQueue;
use Illuminate\Console\Command;

class ExportPollCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var ExportQueue
     */
    private $exportQueueContract;

    /**
     * @var array
     */
    private $exportStages;

    /**
     * ExportPollCommand constructor.
     *
     * @param \App\Repositories\Interfaces\ExportQueue $exportQueueContract
     */
    public function __construct(ExportQueue $exportQueueContract)
    {
        parent::__construct();

        $this->exportQueueContract = $exportQueueContract;
        $this->exportStages = config('config.export_stages');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queues = $this->exportQueueContract->getAllExportQueueOrderByIdAsc();

        $data = ['message' => t('No processes running at this time'), 'payload' => []];

        if ($queues->isEmpty()) {
            PollExportEvent::dispatch($data);

            return;
        }

        $count = 0;
        $data['payload'] = $queues->map(function ($queue) use (&$count) {
            $data = $this->getFirstQueueData($queue);

            $notice = $queue->queued ? $this->setProcessNotice($data) : $this->setQueuedNotice($data, $count);

            $count++;

            return [
                'groupId' => $data->expedition->project->group->id,
                'notice'  => $notice,
            ];
        })->values();

        PollExportEvent::dispatch($data);
    }

    /**
     * Get needed data for queue relationships.
     *
     * @param \App\Models\ExportQueue $queue
     * @return \App\Models\ExportQueue
     */
    private function getFirstQueueData(\App\Models\ExportQueue $queue): \App\Models\ExportQueue
    {
        return $this->exportQueueContract->findQueueProcessData($queue->id, $queue->expedition_id, $queue->actor_id);
    }

    /**
     * Set notice if process is occurring.
     *
     * @param \App\Models\ExportQueue $data
     * @return string
     * @throws \Throwable
     */
    private function setProcessNotice(\App\Models\ExportQueue $data): string
    {
        $stage = $this->exportStages[$data->stage];

        $processed = $data->expedition->actors->first()->pivot->processed === 0 ? 1 : $data->expedition->actors->first()->pivot->processed;

        $stage = GeneralHelper::camelCaseToWords($stage);
        $title = $data->expedition->title;
        $processedRecords = t(' :processed of :total completed.', [
            ':processed' => $processed,
            ':total'     => $data->count,
        ]);

        return view('common.export-process', compact('stage', 'title', 'processedRecords'))->render();
    }

    /**
     * Set notice message for remaining exports.
     *
     * @param \App\Models\ExportQueue $data
     * @param int $count
     * @return string
     * @throws \Throwable
     */
    private function setQueuedNotice(\App\Models\ExportQueue $data, int $count): string
    {
        $title =$data->expedition->title;
        $remainingCount = t(n(':count export remains in queue before processing begins.', ':count exports remain in queue before processing begins.', $count), [':count' => $count]);

        return view('common.export-process-queued', compact('title', 'remainingCount'))->render();
    }
}
