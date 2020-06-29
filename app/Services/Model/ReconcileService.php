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

use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Reconcile;
use App\Repositories\Interfaces\Subject;
use App\Services\Api\PanoptesApiService;
use App\Services\Csv\Csv;
use File;
use Illuminate\Support\Collection;
use League\Csv\Exception;
use Session;
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
     * @var \App\Repositories\Interfaces\Subject
     */
    private $subjectContract;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $apiService;

    /**
     * ReconcileService constructor.
     *
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Reconcile $reconcileContract
     * @param \App\Services\Csv\Csv $csv
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param \App\Services\Api\PanoptesApiService $apiService
     */
    public function __construct(
        Expedition $expeditionContract,
        Reconcile $reconcileContract,
        Csv $csv,
        Subject $subjectContract,
        PanoptesApiService $apiService
    ) {

        $this->expeditionContract = $expeditionContract;
        $this->reconcileContract = $reconcileContract;
        $this->csv = $csv;
        $this->subjectContract = $subjectContract;
        $this->apiService = $apiService;
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
            $recPath = Storage::path(config('config.nfn_downloads_reconcile').'/'.$expeditionId.'.csv');

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
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get data from request.
     */
    public function setData()
    {
        $data = collect(json_decode(request()->get('data'), true))->mapWithKeys(function ($items) {
            return [$items['id'] => explode(',', $items['columns'])];
        });

        Session::put('reconcile', $data);
    }

    /**
     * Get ids from data.
     *
     * @param \Illuminate\Support\Collection $data
     * @return array
     */
    public function getIds(Collection $data): array
    {
        return $data->keys()->map(function ($value) {
            return (string) $value;
        })->toArray();
    }

    /**
     * Get pagination results.
     *
     * @return array
     */
    public function getPagination(): array
    {
        $data = Session::get('reconcile');
        $ids = $this->getIds($data);

        $reconciles = $this->reconcileContract->paginate($ids);

        if ($reconciles->isEmpty() || $reconciles->first()->transcriptions->isEmpty()) {
            return [false, $data];
        }

        return [$reconciles, $data];
    }

    /**
     * Get image url from api or accessURI.
     *
     * @param $reconcile
     * @return mixed
     */
    public function getImageUrl($reconcile)
    {
        $subject = $this->apiService->getPanoptesSubject($reconcile->subject_id);
        $locations = collect($subject['locations'])->filter(function ($location) {
            return ! empty($location['image/jpeg']);
        });

        return $locations->isNotEmpty() ? $locations->first()['image/jpeg'] : $this->getAccessUri($reconcile);
    }

    /**
     * Get image from accessURI.
     *
     * @param $reconcile
     * @return mixed
     */
    public function getAccessUri($reconcile)
    {
        $id = $filename = File::name($reconcile->subject_imageName);
        $subject = $this->subjectContract->find($id, ['accessURI']);

        return $subject->accessURI;
    }

    /**
     * Update reconciled record.
     *
     * @param array $request
     * @return mixed
     */
    public function updateRecord(array $request)
    {
        foreach ($request as $key => $value) {
            $request[$key] = is_null($value) ? '' : $value;
            if (strpos($key, '_radio') !== false) {
                unset($request[$key]);
            }
        }

        return $this->reconcileContract->update($request, $request['_id']);
    }
}