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
use App\Mail\SiteMailer;
use App\Models\PanoptesProject;
use App\Repositories\Interfaces\User;
use App\Services\Csv\Csv;
use App\Services\MongoDbService;
use App\Services\Process\ReconcilePublishService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use MongoDB\Operation\FindOneAndReplace;
use Storage;

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
     * @var \App\Console\Commands\User
     */
    private $userContract;

    /**
     * AppCommand constructor.
     * 109052 panoptes transcriptions without subject_expeditionId
     *
     * @param \App\Services\MongoDbService $service
     */
    public function __construct(
        MongoDbService $service,
        Csv $csv,
        ReconcilePublishService $publishService,
        User $userContract
    ) {
        parent::__construct();
        $this->service = $service;
        $this->expeditionIds = [157];
        $this->csv = $csv;

        $this->regex = config('config.nfn_reconcile_problem_regex');
        $this->publishService = $publishService;
        $this->userContract = $userContract;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        /*
        $users = $this->userContract->getUsersForMailer('owners');
        $recipients = $users->reject(function($user){
            return $user->email === config('mail.from.address');
        })->pluck('email');

        dd($recipients);
        */
        $recipients = [
            'cameron_65@yahoo.com',
            'bruhnrp@yahoo.com',
            'gingernoelsl@gmail.com'
        ];

        Mail::to(config('mail.from.address'))->bcc($recipients)->send(new SiteMailer('This is a test', 'This would be the message that is sent'));
    }

    public function generateCounts()
    {
        $expeditionIds = $this->readDirectory();
        $this->service->setCollection('panoptes_transcriptions_nfndownloads', 'biospex');
        $expeditionIds->each(function ($expeditionId) {
            $counts = $this->getExpeditionCounts($expeditionId);
            if (! empty($counts)) {
                $this->writeRows($expeditionId, $counts, 'counts');
            }
        });
    }

    public function getExpeditionCounts($expeditionId)
    {
        $cursor = $this->service->aggregate([
            ['$match' => ['subject_expeditionId' => (int) $expeditionId]],
            ['$group' => ['_id' => '$subject_id', 'count' => ['$sum' => 1]]],
        ]);

        $counts = [];
        foreach ($cursor as $object) {
            if ($object['count'] > 3) {
                $counts[] = ['subject_id' => $object['_id'], 'count' => $object['count']];
            }
        }

        return $counts;
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
        $results = [];
        $cursor = $this->getAllOldTranscriptions();

        foreach ($cursor as $doc) {
            $exists = $this->checkTranscriptionExists($doc['classification_id'], 'panoptes_transcriptions', 'biospex');
            if ($exists === 0) {
                $expeditionId = isset($doc['subject_expeditionId']) ? $doc['subject_expeditionId'] : 9999;
                $origSubjectCount = $this->getSubjectCount($doc['subject_id'], 'panoptes_transcriptions', 'biospex_dev');
                $newSubjectCount = $this->getSubjectCount($doc['subject_id'], 'panoptes_transcriptions', 'biospex');
                $oldSubjectCount = $this->getSubjectCount($doc['subject_id'], 'panoptes_transcriptions_nfnoldtest', 'biospex');
                $values = [
                    'classification_id'                => $doc['classification_id'],
                    'subject_id'                       => $doc['subject_id'],
                    'old_db_subject_count'             => $origSubjectCount,
                    'new_reconciliation_subject_count' => $newSubjectCount,
                    'old_reconciliation_subject_count' => $oldSubjectCount,
                ];
                $results[$expeditionId][] = $values;
            }
        }

        foreach ($results as $id => $rows) {
            $this->writeRows($id, $rows, 'transcription_counts');
        }

        dd('complete');
    }

    public function writeRows($id, $rows, $dir)
    {
        $this->csv->writerCreateFromPath(storage_path($dir.'/'.$id.'-counts_transcriptions.csv'));
        $header = array_keys($rows[0]);
        $this->csv->insertOne($header);
        $this->csv->insertAll($rows);
    }

    public function getAllOldTranscriptions()
    {
        $this->service->setCollection('panoptes_transcriptions', 'biospex_dev');

        return $this->service->find([]);
    }

    public function getSubjectCount($subjectId, $collection, $db)
    {
        $this->service->setCollection($collection, $db);
        return $this->service->count(['subject_id' => $subjectId]);
    }

    public function checkTranscriptionExists($classificationId, $collection, $db)
    {
        $this->service->setCollection($collection, $db);

        return $this->service->count(['classification_id' => $classificationId]);
    }

    public function readDirectory()
    {
        $expeditionIds = collect();
        $files = \File::files(\Storage::path(config('config.nfn_downloads_classification')));
        foreach ($files as $file) {
            $expeditionIds->push(basename($file, '.csv'));
        }

        return $expeditionIds;
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