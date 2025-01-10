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
use App\Models\BaseEloquentModel;
use App\Models\ExportQueue;
use Illuminate\Console\Command;

/**
 * Class ExportPollCommand
 */
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
     * @var array
     */
    private mixed $exportStages;

    /**
     * ExportPollCommand constructor.
     */
    public function __construct(protected ExportQueue $exportQueue)
    {
        parent::__construct();

        $this->exportStages = config('zooniverse.export_stages');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queues = $this->exportQueue->withCount([
            'files' => function ($q) {
                $q->where('processed', 1);
            },
        ])->with('expedition.project.group')->where('error', 0)->orderBy('id', 'asc')->get();

        $data = ['message' => t('No processes running at this time'), 'payload' => []];

        if ($queues->isEmpty()) {
            PollExportEvent::dispatch($data);

            return;
        }

        $count = 0;
        $data['payload'] = $queues->map(function ($queue) use (&$count) {
            $notice = $queue->queued ? $this->setProcessNotice($queue) : $this->setQueuedNotice($queue, $count);
            $count++;

            return [
                'groupId' => $queue->expedition->project->group->id,
                'notice' => $notice,
            ];
        })->values();

        PollExportEvent::dispatch($data);
    }

    /**
     * Set notice if process is occurring.
     */
    private function setProcessNotice(BaseEloquentModel $queue): string
    {
        $processed = $queue->files_count === 0 ? 1 : $queue->files_count;
        $stage = $this->exportStages[$queue->stage];
        $title = $queue->expedition->title;

        $processedRecords = $queue->stage === 1 ? t(' :processed of :total completed.', [
            ':processed' => $processed,
            ':total' => $queue->total,
        ]) : null;

        return \View::make('common.export-process', compact('stage', 'title', 'processedRecords'))->render();
    }

    /**
     * Set notice message for remaining exports.
     *
     * @throws \Throwable
     */
    private function setQueuedNotice(BaseEloquentModel $queue, int $count): string
    {
        $title = $queue->expedition->title;
        $remainingCount = $count === 0 ? t('Next in queue.') : t(n(':count export in queue before processing begins.', ':count exports in queue before processing begins.', $count), [':count' => $count]);

        return \View::make('common.export-process-queued', compact('title', 'remainingCount'))->render();
    }
}
