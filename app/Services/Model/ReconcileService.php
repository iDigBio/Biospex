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

namespace App\Services\Model;

use App\Notifications\NfnReconciledPublished;
use App\Repositories\Interfaces\Download;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Reconcile;
use App\Services\Csv\Csv;
use Illuminate\Support\Collection;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use Storage;

class ReconcileService
{
    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Repositories\Interfaces\Reconcile
     */
    private $reconcileContract;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * @var \App\Repositories\Interfaces\Download
     */
    private $downloadContract;

    /**
     * ReconcileService constructor.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Reconcile $reconcileContract
     * @param \App\Services\Csv\Csv $csv
     * @param \App\Repositories\Interfaces\Download $downloadContract
     */
    public function __construct(
        Expedition $expeditionContract,
        Reconcile $reconcileContract,
        Csv $csv,
        Download $downloadContract
    )
    {

        $this->expeditionContract = $expeditionContract;
        $this->reconcileContract = $reconcileContract;
        $this->csv = $csv;
        $this->downloadContract = $downloadContract;
    }

    /**
     * Get expedition by id.
     *
     * @param string $expeditionId
     * @return mixed
     */
    public function getExpeditionById(string $expeditionId)
    {
        return $this->expeditionContract->findWith($expeditionId, ['project.group.owner']);
    }

    /**
     * Migrate reconcile csv to mongodb using first or create.
     *
     * @param string $expeditionId
     * @return string|bool
     */
    public function migrateReconcileCsv(string $expeditionId)
    {
        try {
            $recPath = Storage::path(config('config.nfn_downloads_reconcile') . '/' . $expeditionId . '.csv');

            $this->csv->readerCreateFromPath($recPath);
            $this->csv->setDelimiter();
            $this->csv->setEnclosure();
            $this->csv->setHeaderOffset();

            $rows = $this->csv->getRecords($this->csv->getHeader());
            foreach ($rows as $offset => $row) {
                $attributes = ['subject_id' => $row['subject_id'], 'subject_expeditionId' => $expeditionId];
                $this->reconcileContract->firstOrCreate($attributes, $row);
            }

            return false;
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get data from request.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getData(): Collection
    {
        return collect(json_decode(request()->get('data'), true))->mapWithKeys(function($items){
            return [$items['id'] => explode(',', $items['columns'])];
        });
    }

    /**
     * Get ids from data.
     *
     * @param \Illuminate\Support\Collection $data
     * @return array
     */
    public function getIds(Collection $data): array
    {
        return $data->keys()->map(function($value){
            return (string) $value;
        })->toArray();
    }

    /**
     * Get pagination results.
     *
     * @param array $ids
     * @return mixed
     */
    public function getPagination(array $ids)
    {
        $reconciles = $this->reconcileContract->paginate($ids);

        if ($reconciles->isEmpty() || $reconciles->transcriptions->isEmpty() || $reconciles->transcriptions->first()->subject === null) {
            return false;
        }

        return $reconciles;
    }

    /**
     * Update reconciled record.
     *
     * @param array $request
     * @return mixed
     */
    public function updateRecord(array $request)
    {
        foreach($request as $key => $value) {
            $request[$key] = is_null($value) ? '' : $value;
            if (strpos($key, '_radio') !== false) {
                unset($request[$key]);
            }
        }

        return $this->reconcileContract->update($request, $request['_id']);
    }

    /**
     * Publish reconciled file.
     *
     * @param string $expeditionId
     * @throws \Exception
     */
    public function publishReconciled(string $expeditionId)
    {
        $result = $this->createReconcileCsv($expeditionId);
        if (! $result) {
            throw new \Exception(__('Could not create reconcile csv for ' . $expeditionId));
        }

        $this->createDownload($expeditionId);

        $this->sendEmail($expeditionId);
    }

    /**
     * Create csv file for reconciled.
     *
     * @param string $expeditionId
     * @return bool
     */
    private function createReconcileCsv(string $expeditionId): bool
    {
        try{
            $results = $this->reconcileContract->getByExpeditionId($expeditionId);
            $mapped = $results->map(function($record){
                unset($record->_id, $record->updated_at, $record->created_at);
                return $record;
            });

            $header = array_keys($mapped->first()->toArray());

            $fileName = $expeditionId . '.csv';
            $file = Storage::path(config('config.nfn_downloads_reconciled') . '/' . $fileName);
            $this->csv->writerCreateFromPath($file);
            $this->csv->insertOne($header);
            $this->csv->insertAll($mapped->toArray());

            return true;
        } catch (CannotInsertRecord $exception) {
            return false;
        }
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
            'actor_id' => 2,
            'file' => $expeditionId . '.csv',
            'type' => 'reconciled'
        ];
        $attributes = [
            'expedition_id' => $expeditionId,
            'actor_id' => 2,
            'file' => $expeditionId . '.csv',
            'type' => 'reconciled'
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
        $expedition = $this->getExpeditionById($expeditionId);
        $expedition->project->group->owner->notify(new NfnReconciledPublished($expedition->title));
    }
}