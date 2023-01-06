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
use App\Services\Process\AwsS3CsvService;
use Exception;
use File;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Session;
use Storage;
use Validator;

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
    private ReconcileRepository $reconcileRepo;

    /**
     * @var SubjectRepository
     */
    private SubjectRepository $subjectRepo;

    /**
     * @var \App\Services\Process\AwsS3CsvService
     */
    private AwsS3CsvService $awsS3CsvService;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private mixed $problemRegex;

    /**
     * ExpertReconcileProcess constructor.
     *
     * @param \App\Repositories\ReconcileRepository $reconcileRepo
     * @param \App\Repositories\SubjectRepository $subjectRepo
     * @param \App\Services\Process\AwsS3CsvService $awsS3CsvService
     */
    public function __construct(
        ReconcileRepository $reconcileRepo,
        SubjectRepository $subjectRepo,
        AwsS3CsvService $awsS3CsvService
    ) {

        $this->reconcileRepo = $reconcileRepo;
        $this->subjectRepo = $subjectRepo;
        $this->awsS3CsvService = $awsS3CsvService;

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
        $file = config('config.zooniverse_dir.reconcile').'/'.$expeditionId.'.csv';

        if (! Storage::disk('s3')->exists($file)) {
            $message = t('File does not exist.');
            $method = __METHOD__;
            throw new Exception(view('common.exception', compact('message', 'method', 'file')));
        }

        $rows = $this->getCsvRows($file);

        $rows->reject(function ($row) {
            return $this->validateReconcile($row['subject_id']);
        })->each(function ($row) use (&$create) {
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
     * @param string $file
     * @return \Illuminate\Support\Collection
     * @throws \League\Csv\Exception
     */
    public function getCsvRows(string $file): Collection
    {
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $file, 'r');
        $this->awsS3CsvService->createCsvReaderFromStream();
        $this->awsS3CsvService->csv->setHeaderOffset();

        return collect($this->awsS3CsvService->csv->getRecords($this->awsS3CsvService->csv->getHeader()));
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPagination(int $expeditionId): LengthAwarePaginator
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
    public function getImageUrl(string $imageName, string $location = null): mixed
    {
        return $location !== null ? $location : $this->getAccessUri($imageName);
    }

    /**
     * Get image from accessURI.
     *
     * @param $imageName
     * @return mixed
     */
    public function getAccessUri($imageName): mixed
    {
        $id = File::name($imageName);
        $subject = $this->subjectRepo->find($id, ['accessURI']);

        return $subject->accessURI;
    }

    /**
     * Update reconciled record.
     *
     * Unset unneeded variables and decode columns. Use str_place for some columns with spaces.
     *
     * @param array $request
     * @return mixed
     */
    public function updateRecord(array $request): mixed
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
        $file = config('config.zooniverse_dir.explained').'/'.$expeditionId.'.csv';

        if (! Storage::disk('s3')->exists($file)) {
            $message = t('File does not exist.');
            $method = __METHOD__;
            throw new Exception(view('common.exception', compact('message', 'method', 'file')));
        }

        $rows = $this->getCsvRows($file);

        $rows->mapWithKeys(function ($row) {
            $problems = collect($row)->filter(function ($value, $field) {
                return $this->checkForProblem($value, $field);
            });

            return [$row['subject_id'] => $problems];
        })->reject(function ($problem) {
            return $problem->isEmpty();
        })->mapWithKeys(function ($problem, $subjectId) {
            $string = $problem->keys()->map(function ($key) {
                $trimmedKey = trim(str_replace('Explanation', '', $key));

                return TranscriptionMapHelper::encodeTranscriptionField($trimmedKey);
            })->flatten()->join('|');

            return [$subjectId => $string];
        })->each(function ($columns, $subjectId) {
            $reconcile = $this->reconcileRepo->findBy('subject_id', $subjectId);
            if ($reconcile === null) {
                return;
            }
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