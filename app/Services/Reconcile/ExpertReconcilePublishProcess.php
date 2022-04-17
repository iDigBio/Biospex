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
use App\Notifications\ExpertReviewPublished;
use App\Repositories\DownloadRepository;
use App\Repositories\ExpeditionRepository;
use App\Repositories\ReconcileRepository;
use App\Services\Csv\Csv;
use Storage;

/**
 * Class ExpertReconcilePublishProcess
 *
 * @package App\Services\Process
 */
class ExpertReconcilePublishProcess
{
    /**
     * @var \App\Repositories\ReconcileRepository
     */
    private $reconcileRepo;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepo;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private $expeditionRepo;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * ExpertReconcilePublishService constructor.
     *
     * @param \App\Repositories\ReconcileRepository $reconcileRepo
     * @param \App\Repositories\DownloadRepository $downloadRepo
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        ReconcileRepository $reconcileRepo,
        DownloadRepository $downloadRepo,
        ExpeditionRepository $expeditionRepo,
        Csv $csv
    )
    {
        $this->reconcileRepo = $reconcileRepo;
        $this->downloadRepo = $downloadRepo;
        $this->expeditionRepo = $expeditionRepo;
        $this->csv = $csv;
    }

    /**
     * Publish reconciled file.
     *
     * @param string $expeditionId
     * @throws \League\Csv\CannotInsertRecord
     */
    public function publishReconciled(string $expeditionId)
    {
        $this->createReconcileCsv($expeditionId);
        $this->createDownload($expeditionId);
        $this->sendEmail($expeditionId);
    }

    /**
     * Create csv file for reconciled.
     *
     * @param string $expeditionId
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    private function createReconcileCsv(string $expeditionId)
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

        $fileName = $expeditionId.'.csv';
        $file = Storage::path(config('config.nfn_downloads_reconciled').'/'.$fileName);
        $this->csv->writerCreateFromPath($file);
        $this->csv->insertOne($decodedHeader);
        $this->csv->insertAll($mapped->toArray());
    }

    /**
     * Create download file.
     *
     * @param string $expeditionId
     */
    private function createDownload(string $expeditionId)
    {
        $values = [
            'expedition_id' => $expeditionId,
            'actor_id'      => 2,
            'file'          => $expeditionId.'.csv',
            'type'          => 'reconciled',
        ];
        $attributes = [
            'expedition_id' => $expeditionId,
            'actor_id'      => 2,
            'file'          => $expeditionId.'.csv',
            'type'          => 'reconciled',
        ];

        $this->downloadRepo->updateOrCreate($attributes, $values);
    }

    /**
     * Send email to project owner.
     *
     * @param string $expeditionId
     */
    private function sendEmail(string $expeditionId)
    {
        $expedition = $this->expeditionRepo->findWith($expeditionId, ['project.group.owner']);
        $expedition->project->group->owner->notify(new ExpertReviewPublished($expedition->title));
    }
}