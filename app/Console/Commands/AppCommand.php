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

use App\Jobs\Traits\SkipNfn;
use App\Models\PanoptesProject;
use App\Notifications\JobError;
use App\Repositories\Interfaces\Expedition;
use App\Services\Api\PanoptesApiService;
use App\Services\Csv\Csv;
use App\Services\Csv\ExpertReconcileCsv;
use App\Services\Model\PusherTranscriptionService;
use App\Services\MongoDbService;
use App\Services\Process\PanoptesTranscriptionProcess;
use Illuminate\Console\Command;
use MongoDB\Operation\FindOneAndReplace;
use SebastianBergmann\CodeCoverage\Report\PHP;

class AppCommand extends Command
{
    use SkipNfn;

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
     * @var \App\Services\Csv\ExpertReconcileCsv
     */
    private $expertReconcileCsv;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

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
        PanoptesTranscriptionProcess $panoptesTranscriptionProcess,
        ExpertReconcileCsv $expertReconcileCsv,
        Csv $csv
    ) {
        parent::__construct();
        $this->service = $service;
        $this->expeditionIds = [157];
        $this->expeditionContract = $expeditionContract;
        $this->panoptesApiService = $panoptesApiService;
        $this->pusherTranscriptionService = $pusherTranscriptionService;
        $this->panoptesTranscriptionProcess = $panoptesTranscriptionProcess;
        $this->expertReconcileCsv = $expertReconcileCsv;
        $this->csv = $csv;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        //$this->checkPanoptesWithPusher();
        //$this->checkPanoptesToPanoptes();
        $this->findWorkflowsMissingCsv();

        /*
        $text = explode(',', config('config.nfn_reconcile_problem_match'));

        foreach ($text as $string) {
            if (preg_match('/No (?:select|text) match on|Only 1 transcript in|There was 1 number in/i', $string, $matches)) {
                echo $matches[0] . PHP_EOL;
            }
        }
        */

    }

    public function findWorkflowsMissingCsv()
    {
        $results = PanoptesProject::whereNotNull('expedition_id')->get();
        $rejected = $results->reject(function ($result){
            return \Storage::exists(config('config.nfn_downloads_classification') . '/' . $result->expedition_id . '.csv');
        });

        dd($rejected->pluck('expedition_id'));
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
        $this->csv->writerCreateFromPath(storage_path('missing_transcriptions.csv'));
        $rows = [];

        $this->service->setCollection('panoptes_transcriptions', 'biospex_dev');
        $cursor = $this->service->find([]);
        foreach($cursor as $doc) {
            $this->service->setCollection('panoptes_transcriptions');
            $count = $this->service->count(['classification_id' => $doc['classification_id']]);
            if (!$count){
                $expedition = isset($doc['subject_expeditionId']) ? $doc['subject_expeditionId'] : '';
                $rows[] = ['expedition' => $expedition, 'classification_id' =>  $doc['classification_id']];
            }
        }
        $header = array_keys($rows[0]);
        $this->csv->insertOne($header);
        foreach ($rows as $row) {
            $this->csv->insertOne($row);
        }
        dd('complete');
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