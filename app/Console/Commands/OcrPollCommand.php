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

namespace App\Console\Commands;

use App\Events\PollOcrEvent;
use App\Repositories\Interfaces\Project;
use Illuminate\Console\Command;
use App\Repositories\Interfaces\OcrQueue;

class OcrPollCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocr:poll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes information for OCR Polling event.';

    /**
     * @var OcrQueue
     */
    private $ocrQueueContract;

    /**
     * @var Project
     */
    private $projectContract;

    /**
     * Create a new command instance.
     *
     * @param OcrQueue $ocrQueueContract
     * @param Project $projectContract
     */
    public function __construct(
        OcrQueue $ocrQueueContract,
        Project $projectContract
    )
    {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
        $this->projectContract = $projectContract;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = $this->ocrQueueContract->getOcrQueuesForPollCommand();

        $data = ['message' => t('No processes running at this time'), 'payload' => []];

        if ($records->isEmpty())
        {
            PollOcrEvent::dispatch($data);

            return;
        }

        $count = 0;
        $data['payload'] = $records->map(function($record) use (&$count){
            $batches = $count === 0 ? '' : t(n(':batches_queued process remains in queue before processing begins', ':batches_queued processes remain in queue before processing begins', $count), [':batches_queued' => $count]);

            $countNumbers = [':processed' => $record->processed, ':total' => $record->total];
            $ocr = t(n(':processed record of :total completed.', ':processed records of :total completed.', $record->processed), $countNumbers);

            $title = $record->expedition !== null ? $record->expedition->title : $record->project->title;

            $notice = view('common.ocr-process', compact('title','ocr', 'batches'))->render();

            $count++;

            return [
                'groupId' => $record->project->group->id,
                'notice'   => $notice,
            ];
        })->toArray();

        PollOcrEvent::dispatch($data);
    }
}
