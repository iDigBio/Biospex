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

use App\Jobs\ATestJob;
use App\Jobs\TestJob;
use App\Models\Expedition;
use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Repositories\ExpeditionRepository;
use App\Repositories\ExportQueueFileRepository;
use App\Services\Actor\ActorFactory;
use Illuminate\Console\Command;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        ExpeditionRepository $expeditionRepository,
        ExportQueueFileRepository $exportQueueFileRepository
    ) {
        parent::__construct();
        $this->expeditionRepository = $expeditionRepository;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
    }

    /**
     *
     */
    public function handle()
    {
        TestJob::dispatch();
        //$queue = ExportQueue::with(['expedition', 'actor'])->find(1);
        //dd($queue->actor->id);

        //$expedition = $this->expeditionRepository->findwith(422, ['nfnActor', 'stat']);
        //ActorFactory::create($expedition->nfnActor->class)->actor($expedition->nfnActor);

    }

    /**
     * Create data array.
     *
     * @param \App\Models\ExportQueueFile $file
     * @return array
     */
    #[ArrayShape(['queueId' => "mixed", 'subjectId' => "mixed", 'url' => "mixed", 'dir' => "string"])]
    private function createDataArray(ExportQueueFile $file): array
    {
        return [
            'queueId'   => $file->queue_id,
            'subjectId' => $file->subject_id,
            'url'       => $file->url,
            'dir'       => 'testing',
        ];
    }

}