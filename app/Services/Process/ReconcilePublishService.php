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

namespace App\Services\Process;

use App\Notifications\NfnExpertReviewPublished;
use App\Repositories\Interfaces\Download;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Reconcile;
use App\Services\Csv\Csv;
use League\Csv\CannotInsertRecord;
use Storage;

class ReconcilePublishService
{
    /**
     * @var \App\Repositories\Interfaces\Reconcile
     */
    private $reconcileContract;

    /**
     * @var \App\Repositories\Interfaces\Download
     */
    private $downloadContract;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * ExpertReconcilePublishService constructor.
     *
     * @param \App\Repositories\Interfaces\Reconcile $reconcileContract
     * @param \App\Repositories\Interfaces\Download $downloadContract
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        Reconcile $reconcileContract,
        Download $downloadContract,
        Expedition $expeditionContract,
        Csv $csv
    )
    {
        $this->reconcileContract = $reconcileContract;
        $this->downloadContract = $downloadContract;
        $this->expeditionContract = $expeditionContract;
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
        $results = $this->reconcileContract->getByExpeditionId($expeditionId);
        $mapped = $results->map(function ($record) {
            unset($record->_id, $record->columns, $record->problem, $record->updated_at, $record->created_at);
            return $record;
        });

        if ($mapped->isEmpty()) {
            throw new \Exception(t('Missing reconciled records for Expert Review publish for Expedition Id: %s', $expeditionId));
        }

        $header = array_keys($mapped->first()->toArray());

        $fileName = $expeditionId.'.csv';
        $file = Storage::path(config('config.nfn_downloads_reconciled').'/'.$fileName);
        $this->csv->writerCreateFromPath($file);
        $this->csv->insertOne($header);
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

        $this->downloadContract->updateOrCreate($attributes, $values);
    }

    /**
     * Send email to project owner.
     *
     * @param string $expeditionId
     */
    private function sendEmail(string $expeditionId)
    {
        $expedition = $this->expeditionContract->findWith($expeditionId, ['project.group.owner']);
        $expedition->project->group->owner->notify(new NfnExpertReviewPublished($expedition->title));
    }
}