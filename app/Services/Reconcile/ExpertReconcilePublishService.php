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
use App\Repositories\ReconcileRepository;
use App\Services\Csv\AwsS3CsvService;

/**
 * Class ExpertReconcilePublishService
 *
 * @package App\Services\Process
 */
class ExpertReconcilePublishService
{
    /**
     * @var \App\Repositories\ReconcileRepository
     */
    private ReconcileRepository $reconcileRepo;

    /**
     * @var \App\Services\Csv\AwsS3CsvService
     */
    private AwsS3CsvService $awsS3CsvService;

    /**
     * @var \App\Services\Reconcile\ReconcileService
     */
    private ReconcileService $reconcileService;

    /**
     * ExpertReconcilePublishService constructor.
     *
     * @param \App\Repositories\ReconcileRepository $reconcileRepo
     * @param \App\Services\Csv\AwsS3CsvService $awsS3CsvService
     * @param \App\Services\Reconcile\ReconcileService $reconcileService
     */
    public function __construct(
        ReconcileRepository $reconcileRepo,
        AwsS3CsvService $awsS3CsvService,
        ReconcileService $reconcileService
    )
    {
        $this->reconcileRepo = $reconcileRepo;
        $this->awsS3CsvService = $awsS3CsvService;
        $this->reconcileService = $reconcileService;
    }

    /**
     * Publish reconciled file.
     *
     * @param string $expeditionId
     * @throws \League\Csv\CannotInsertRecord
     */
    public function publishReconciled(string $expeditionId): void
    {
        $this->createReconciledWithExpertCsv($expeditionId);
        $this->reconcileService->updateOrCreateReviewDownload($expeditionId, config('zooniverse.directory.reconciled-with-expert'));
    }

    /**
     * Create csv file for reconciled.
     *
     * @param string $expeditionId
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    private function createReconciledWithExpertCsv(string $expeditionId): void
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
        $decodedHeader  = [];
        foreach ($header as $value) {
            $decodedHeader[] = TranscriptionMapHelper::decodeTranscriptionField($value);
        }

        $file = config('zooniverse.directory.reconciled-with-expert') . '/' . $expeditionId.'.csv';
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $file, 'w');
        $this->awsS3CsvService->createCsvWriterFromStream();
        $this->awsS3CsvService->csv->insertOne($decodedHeader);
        $this->awsS3CsvService->csv->insertAll($mapped->toArray());
    }
}