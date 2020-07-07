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

use App\Repositories\Interfaces\Expedition;
use App\Services\Api\PanoptesApiService;
use App\Services\Model\PusherTranscriptionService;
use App\Services\MongoDbService;
use App\Services\Process\PanoptesTranscriptionProcess;
use Illuminate\Console\Command;
use MongoDB\Operation\FindOneAndReplace;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Services\MongoDbService
     */
    private $service;

    private $expeditionIds;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $panoptesApiService;

    /**
     * @var \App\Services\Model\PusherTranscriptionService
     */
    private $pusherTranscriptionService;

    /**
     * @var \App\Services\Process\PanoptesTranscriptionProcess
     */
    private $panoptesTranscriptionProcess;

    /**
     * AppCommand constructor.
     * 109052 panoptes transcriptions without subject_expeditionId
     *
     * @param \App\Services\MongoDbService $service
     */
    public function __construct(
        MongoDbService $service,
        Expedition $expeditionContract,
        PanoptesApiService $panoptesApiService,
        PusherTranscriptionService $pusherTranscriptionService,
        PanoptesTranscriptionProcess $panoptesTranscriptionProcess
    ) {
        parent::__construct();
        $this->service = $service;
        $this->expeditionIds = [157];
        $this->expeditionContract = $expeditionContract;
        $this->panoptesApiService = $panoptesApiService;
        $this->pusherTranscriptionService = $pusherTranscriptionService;

        $this->panoptesTranscriptionProcess = $panoptesTranscriptionProcess;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        //$this->checkPanoptesWithPusher();
        $this->checkPanoptesToPanoptes();

    }

    public function checkPanoptesWithPusher()
    {
        $this->service->setCollection('panoptes_transcriptions');
        $cursor = $this->service->find([]);
        $i=0;
        foreach($cursor as $doc) {
            $this->service->setCollection('pusher_transcriptions');
            $count = $this->service->count(['classification_id' => $doc['classification_id']]);
            if (!$count){
                \Log::alert($doc['subject_expeditionId'] . ': ' . $doc['classification_id']);
                $i++;
            }
        }
        dd($i);
    }

    public function checkPanoptesToPanoptes()
    {
        $this->service->setCollection('panoptes_transcriptions', 'biospex_dev');
        $cursor = $this->service->find([]);
        $i = 0;
        foreach($cursor as $doc) {
            $this->service->setCollection('panoptes_transcriptions');
            $count = $this->service->count(['classification_id' => $doc['classification_id']]);
            if (!$count){
                \Log::alert($doc['classification_id']);
                $i++;
            }
        }
        dd($i);
    }

    public function findAndReplace()
    {
        $filter = [
            '_id'               => $this->service->setMongoObjectId('5eff4cf04dae5813ca629999'),
            'classification_id' => '999999',
        ];
        $replacement = ['classification_id' => '987455', 'field5' => 'test', 'field6' => 'example'];
        $options = ['upsert' => true, 'returnNewDocument' => FindOneAndReplace::RETURN_DOCUMENT_AFTER];
        $result = $this->service->findOneAndReplace($filter, $replacement, $options);
        dd($result);
    }
}