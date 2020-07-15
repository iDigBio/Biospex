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
use App\Services\Csv\Csv;
use App\Services\MongoDbService;
use App\Services\Process\ReconcilePublishService;
use Illuminate\Console\Command;
use MongoDB\Operation\FindOneAndReplace;

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
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $regex;

    /**
     * @var \App\Services\Process\ReconcilePublishService
     */
    private $publishService;

    /**
     * AppCommand constructor.
     * 109052 panoptes transcriptions without subject_expeditionId
     *
     * @param \App\Services\MongoDbService $service
     */
    public function __construct(
        MongoDbService $service,
        Csv $csv,
        ReconcilePublishService $publishService
    ) {
        parent::__construct();
        $this->service = $service;
        $this->expeditionIds = [157];
        $this->csv = $csv;

        $this->regex = config('config.nfn_reconcile_problem_regex');
        $this->publishService = $publishService;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        //$this->checkPanoptesWithPusher();
        //$this->checkPanoptesToPanoptes();
        //$this->findWorkflowsMissingCsv();

        $this->publishService->publishReconciled(80);
    }


    public function findWorkflowsMissingCsv()
    {
        $results = PanoptesProject::whereNotNull('expedition_id')->get();
        $rejected = $results->reject(function ($result) {
            return \Storage::exists(config('config.nfn_downloads_classification').'/'.$result->expedition_id.'.csv');
        });

        dd($rejected->pluck('expedition_id'));
    }

    public function checkPanoptesWithPusher()
    {
        $this->service->setCollection('panoptes_transcriptions');
        $cursor = $this->service->find([]);
        $i = 0;
        foreach ($cursor as $doc) {
            $this->service->setCollection('pusher_transcriptions');
            $count = $this->service->count(['classification_id' => $doc['classification_id']]);
            if (! $count) {
                \Log::alert($doc['subject_expeditionId'].': '.$doc['classification_id']);
                $i++;
            }
        }
        dd($i);
    }

    public function checkPanoptesToPanoptes()
    {
        $rows = [];

        $this->service->setCollection('panoptes_transcriptions', 'biospex_dev');
        $dev_cursor = $this->service->find([]);
        foreach ($dev_cursor as $doc) {
            $this->service->setCollection('panoptes_transcriptions', 'biospex');
            $count = $this->service->count(['classification_id' => $doc['classification_id']]);
            if ($count === 0) {
                $expeditionId = isset($doc['subject_expeditionId']) ? $doc['subject_expeditionId'] : 9999;
                $subject_count = $this->service->count(['subject_id' => $doc['subject_id']]);
                $values = [
                    'classification_id' => $doc['classification_id'],
                    'subject_id'        => $doc['subject_id'],
                    'subject_count'     => $subject_count,
                ];
                $rows[$expeditionId][] = $values;
            }
        }

        foreach ($rows as $key => $newRows) {

            $this->csv->writerCreateFromPath(storage_path('testing/'.$key.'-missing_transcriptions.csv'));

            $header = array_keys($newRows[0]);
            $this->csv->insertOne($header);

            foreach ($newRows as $newRow) {
                $this->csv->insertOne($newRow);
            }
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