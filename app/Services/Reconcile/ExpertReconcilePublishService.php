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

namespace App\Services\Reconcile;

use App\Facades\TranscriptionMapHelper;
use App\Notifications\Generic;
use App\Repositories\DownloadRepository;
use App\Repositories\ExpeditionRepository;
use App\Repositories\ReconcileRepository;
use App\Services\Csv\AwsS3CsvService;

/**
 * Class ExpertReconcilePublishService
 */
class ExpertReconcilePublishService
{
    private ReconcileRepository $reconcileRepo;

    private DownloadRepository $downloadRepo;

    private ExpeditionRepository $expeditionRepo;

    private AwsS3CsvService $awsS3CsvService;

    /**
     * ExpertReconcilePublishService constructor.
     */
    public function __construct(
        ReconcileRepository $reconcileRepo,
        DownloadRepository $downloadRepo,
        ExpeditionRepository $expeditionRepo,
        AwsS3CsvService $awsS3CsvService
    ) {
        $this->reconcileRepo = $reconcileRepo;
        $this->downloadRepo = $downloadRepo;
        $this->expeditionRepo = $expeditionRepo;
        $this->awsS3CsvService = $awsS3CsvService;
    }

    /**
     * Publish reconciled file.
     *
     * @see \App\Jobs\ExpertReconcileReviewPublishJob
     *
     * @throws \League\Csv\CannotInsertRecord
     */
    public function publishReconciled(string $expeditionId): void
    {
        $this->createReconcileCsv($expeditionId);
        $this->createDownload($expeditionId);
        $this->sendEmail($expeditionId);
    }

    /**
     * Create csv file for reconciled.
     *
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    private function createReconcileCsv(string $expeditionId): void
    {
        $results = $this->reconcileRepo->getBy('subject_expeditionId', (int) $expeditionId);
        $mapped = $results->map(function ($record) {
            unset($record->_id, $record->subject_columns, $record->subject_problem, $record->updated_at, $record->created_at, $record->reviewed);

            return $record;
        });

        if ($mapped->isEmpty()) {
            throw new \Exception(t('Missing reconciled records for Expert Review publish for Expedition Id: %s', $expeditionId));
        }

        $header = array_keys($mapped->first()->toArray());
        $decodedHeader = [];
        foreach ($header as $value) {
            $decodedHeader[] = TranscriptionMapHelper::decodeTranscriptionField($value);
        }

        $file = config('zooniverse.directory.reconciled-with-expert').'/'.$expeditionId.'.csv';
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $file, 'w');
        $this->awsS3CsvService->createCsvWriterFromStream();
        $this->awsS3CsvService->csv->insertOne($decodedHeader);
        $this->awsS3CsvService->csv->insertAll($mapped->toArray());
    }

    /**
     * Create download file.
     */
    private function createDownload(string $expeditionId): void
    {
        $values = [
            'expedition_id' => $expeditionId,
            'actor_id' => 2,
            'file' => $expeditionId.'.csv',
            'type' => 'reconciled-with-expert',
        ];
        $attributes = [
            'expedition_id' => $expeditionId,
            'actor_id' => 2,
            'file' => $expeditionId.'.csv',
            'type' => 'reconciled-with-expert',
        ];

        $this->downloadRepo->updateOrCreate($attributes, $values);
    }

    /**
     * Send email to project owner.
     */
    private function sendEmail(string $expeditionId): void
    {
        $expedition = $this->expeditionRepo->findWith($expeditionId, ['project.group.owner']);

        $attributes = [
            'subject' => t('Reconciled Expert Review Published'),
            'html' => [
                t('The Expert Reviewed Reconciled CSV file has been published for %s.', $expedition->title),
                t('The file can be downloaded in the Downloads section of the Expedition page.'),
            ],
        ];

        $expedition->project->group->owner->notify(new Generic($attributes));
    }
}
