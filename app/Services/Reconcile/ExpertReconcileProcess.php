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
use App\Repositories\SubjectRepository;
use App\Services\Csv\Csv;
use Exception;
use File;
use Illuminate\Support\Collection;
use Session;
use Storage;
use Validator;
use function collect;
use function config;
use function t;
use function view;

/**
 * Class ExpertReconcileProcess
 *
 * @package App\Services\Process
 */
class ExpertReconcileProcess
{
    /**
     * @var \App\Repositories\ReconcileRepository
     */
    private $reconcileRepo;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * @var SubjectRepository
     */
    private $subjectRepo;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $problemRegex;

    /**
     * ExpertReconcileProcess constructor.
     *
     * @param \App\Repositories\ReconcileRepository $reconcileRepo
     * @param \App\Services\Csv\Csv $csv
     * @param \App\Repositories\SubjectRepository $subjectRepo
     */
    public function __construct(
        ReconcileRepository $reconcileRepo,
        Csv $csv,
        SubjectRepository $subjectRepo
    ) {

        $this->reconcileRepo = $reconcileRepo;
        $this->csv = $csv;
        $this->subjectRepo = $subjectRepo;

        $this->problemRegex = config('config.nfn_reconcile_problem_regex');
    }

    /**
     * Migrate reconcile csv to mongodb using first or create.
     *
     * @param string $expeditionId
     * @throws \League\Csv\Exception|\Exception
     */
    public function migrateReconcileCsv(string $expeditionId)
    {
        $file = config('config.nfn_downloads_reconcile').'/'.$expeditionId.'.csv';

        if (!Storage::exists($file)) {
            $message = t('File does not exist.');
            $method = __METHOD__;
            throw new Exception(view('common.exception', compact('message', 'method', 'file')));
        }

        $filePath = Storage::path($file);
        $rows = $this->getCsvRows($filePath);
        $rows->reject(function($row){
            return $this->validateReconcile($row['subject_id']);
        })->each(function($row) {
            $newRecord = [];
            foreach ($row as $field => $value) {
                $newField = TranscriptionMapHelper::encodeTranscriptionField($field);
                $newRecord[$newField] = $value;
                $newRecord['subject_problem'] = 0;
                $newRecord['subject_columns'] = '';
            }

            $this->reconcileRepo->create($newRecord);
        });
    }

    /**
     * Get csv rows from file.
     *
     * @param $filePath
     * @return \Illuminate\Support\Collection
     * @throws \League\Csv\Exception
     */
    public function getCsvRows($filePath): Collection
    {
        $this->csv->readerCreateFromPath($filePath);
        $this->csv->setHeaderOffset();

        return collect($this->csv->getRecords($this->csv->getHeader()));
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
     * @param int $expeditionId
     * @return mixed
     */
    public function getPagination(int $expeditionId)
    {
        return $this->reconcileRepo->paging($expeditionId);
    }

    /**
     * Get image url from api or accessURI.
     *
     * @param string $imageName
     * @param string|null $location
     * @return mixed
     */
    public function getImageUrl(string $imageName, string $location = null)
    {
        return $location !== null ? $location : $this->getAccessUri($imageName);
    }

    /**
     * Get image from accessURI.
     *
     * @param $imageName
     * @return mixed
     */
    public function getAccessUri($imageName)
    {
        $id = File::name($imageName);
        $subject = $this->subjectRepo->find($id, ['accessURI']);

        return $subject->accessURI;
    }

    /**
     * Create masked columns for names.
     *
     * When posting names with spaces, PHP converts to underscore. So we mask, and demask on update.
     * @param string $columns
     * @return array
     */
    public function setColumnMasks(string $columns)
    {
        $columnArray = explode(',', $columns);
        sort($columnArray);
        $maskedColumns = [];
        foreach($columnArray as $column) {
            $maskedColumns[base64_encode($column)] = $column;
        }

        return $maskedColumns;
    }

    /**
     * Update reconciled record.
     *
     * Unset unneeded variables and decode columns. Use str_place for some columns with spaces.
     *
     * @param array $request
     * @return mixed
     */
    public function updateRecord(array $request)
    {
        $id = $request['_id'];
        unset($request['_id'], $request['_method'], $request['_token'], $request['page'], $request['radio']);

        $attributes = [];
        foreach ($request as $key => $value) {
            $attributes[str_replace('--', ' ', $key)] = is_null($value) ? '' : $value;
        }
        $attributes['reviewed'] = 1;

        return $this->reconcileRepo->update($attributes, $id);
    }

    /**
     * Set problem columns in reconcile documents.
     *
     * @param int $expeditionId
     * @throws \League\Csv\Exception|\Exception
     */
    public function setReconcileProblems(int $expeditionId)
    {
        $file = config('config.nfn_downloads_explained').'/'.$expeditionId.'.csv';

        if (! Storage::exists($file)) {
            $message = t('File does not exist.');
            $method = __METHOD__;
            throw new Exception(view('common.exception', compact('message', 'method', 'file')));
        }

        $filePath = Storage::path($file);

        $rows = $this->getCsvRows($filePath);

        $rows->mapWithKeys(function ($row) {
            $problems = collect($row)->filter(function ($value, $field) {
                return $this->checkForProblem($value, $field);
            });

            return [$row['subject_id'] => $problems];
        })->reject(function ($problem) {
            return $problem->isEmpty();
        })->mapWithKeys(function ($problem, $subjectId) {
            $string = $problem->keys()->map(function ($key) {
                $trimmedKey =  trim(str_replace('Explanation', '', $key));
                return TranscriptionMapHelper::encodeTranscriptionField($trimmedKey);
            })->flatten()->join('|');

            return [$subjectId => $string];
        })->each(function ($columns, $subjectId) {
            $reconcile = $this->reconcileRepo->findBy('subject_id', $subjectId);
            $reconcile->subject_problem = 1;
            $reconcile->subject_columns = $columns;
            $reconcile->save();
        });
    }

    /**
     * Check columns for problems.
     *
     * Regex match = /No (?:select|text) match on|Only 1 transcript in|There was 1 number in/i
     *
     * @param string $value
     * @param string $field
     * @return bool
     */
    private function checkForProblem(string $value, string $field): bool
    {
        return str_contains($field, 'Explanation') && preg_match($this->problemRegex, $value);
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $subject_id
     * @return bool
     */
    private function validateReconcile($subject_id): bool
    {
        $rules = ['subject_id' => 'unique:mongodb.reconciles,subject_id'];
        $values = ['subject_id' => (int) $subject_id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if record exists.
        return $validator->fails();
    }
}