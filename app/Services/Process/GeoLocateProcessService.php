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

namespace App\Services\Process;

use App\Models\Expedition;
use App\Models\GeoLocateForm;
use App\Repositories\ExpeditionRepository;
use App\Repositories\GeoLocateFormRepository;
use App\Repositories\GeoLocateRepository;
use App\Services\Csv\AwsS3CsvService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Storage;

class GeoLocateProcessService
{
    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * @var \App\Repositories\GeoLocateFormRepository
     */
    private GeoLocateFormRepository $geoLocateFormRepository;

    /**
     * @var \App\Services\Csv\AwsS3CsvService
     */
    private AwsS3CsvService $awsS3CsvService;

    /**
     * @var string
     */
    private string $source;

    /**
     * @var bool
     */
    private bool $expertFileExists;

    /**
     * @var bool
     */
    private bool $expertReviewExists;

    /**
     * @var \App\Repositories\GeoLocateRepository
     */
    private GeoLocateRepository $geoLocateRepository;

    /**
     * @var true
     */
    private bool $mismatchSource = false;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExpeditionRepository $expeditionRepository
     * @param \App\Repositories\GeoLocateFormRepository $geoLocateFormRepository
     * @param \App\Services\Csv\AwsS3CsvService $awsS3CsvService
     * @param \App\Repositories\GeoLocateRepository $geoLocateRepository
     */
    public function __construct(
        ExpeditionRepository $expeditionRepository,
        GeoLocateFormRepository $geoLocateFormRepository,
        AwsS3CsvService $awsS3CsvService,
        GeoLocateRepository $geoLocateRepository

    ) {

        $this->expeditionRepository = $expeditionRepository;
        $this->geoLocateFormRepository = $geoLocateFormRepository;
        $this->awsS3CsvService = $awsS3CsvService;
        $this->geoLocateRepository = $geoLocateRepository;
    }

    /**
     * Find project with relations.
     *
     * @param int $expeditionId
     * @param array $relations
     * @return \App\Models\Expedition
     */
    public function findExpeditionWithRelations(int $expeditionId, array $relations = []): Expedition
    {
        return $this->expeditionRepository->findWith($expeditionId, $relations);
    }

    /**
     * Get the form based on new or existing.
     *
     * @param \App\Models\Expedition $expedition
     * @param array $request
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getForm(Expedition $expedition, array $request): array
    {
        $record = isset($request['formId']) ? $this->findGeoLocateFormById($request['formId']) : null;

        $this->setExpertExistVars($expedition);

        $this->setSource($request, $record);

        return $record === null ? $this->newForm($expedition) : $this->existingForm($record, $expedition);
    }

    /**
     * Return form for selected destination.
     *
     * @param \App\Models\Expedition $expedition
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function newForm(Expedition $expedition): array
    {
        return [
            'group_id'        => $expedition->project->group->id,
            'name'            => '',
            'source'          => $this->source,
            'entries'         => old('entries', 1),
            'fields'          => null,
            'expert_file'     => $this->expertFileExists,
            'expert_review'   => $this->expertReviewExists,
            'exported'        => ! empty($expedition->geoLocateActor->pivot->state),
            'geo'             => $this->getGeoLocateFields(),
            'csv'             => $this->getCsvHeader($expedition),
            'mismatch_source' => $this->mismatchSource,
        ];
    }

    /**
     * Return form from existing form.
     *
     * @param \App\Models\GeoLocateForm $record
     * @param \App\Models\Expedition $expedition
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function existingForm(GeoLocateForm $record, Expedition $expedition): array
    {
        if ($record->source !== $this->source) {
            $this->mismatchSource = true;

            return $this->newForm($expedition);
        }

        $entries = count($record->fields);

        return [
            'group_id'      => $record->group_id,
            'name'          => $record->name,
            'source'        => $this->source,
            'entries'       => $entries,
            'fields'        => $record->fields,
            'expert_file'   => $this->expertFileExists,
            'expert_review' => $this->expertReviewExists,
            'exported'      => ! empty($expedition->geoLocateActor->pivot->state),
            'geo'           => $this->getGeoLocateFields(),
            'csv'           => $this->getCsvHeader($expedition),
            'mismatch_source' => $this->mismatchSource,
        ];
    }

    /**
     * Save the export form data.
     *
     * @param array $request
     * @param \App\Models\Expedition $expedition
     * @return void
     */
    public function saveForm(array $request, Expedition $expedition): void
    {
        $hash = md5(json_encode($request['fields']));

        $attributes = [
            'group_id' => $request['group_id'],
            'name'     => $request['name'],
            'source'   => $request['source'],
            'hash'     => $hash,
        ];
        $values = [
            'group_id' => $request['group_id'],
            'name'     => $request['name'],
            'source'   => $request['source'],
            'hash'     => $hash,
            'fields'   => $request['fields'],
        ];

        $geoLocateForm = $this->geoLocateFormRepository->updateOrCreate($attributes, $values);
        $expedition->geoLocateForm()->associate($geoLocateForm)->save();
    }

    /**
     * Get GeoLocate fields from file.
     *
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getGeoLocateFields(): array
    {
        return json_decode(File::get(config('config.geolocate_fields_file')), true);
    }

    /**
     * Set source by using formData.
     *
     * @param array $request
     * @param \App\Models\GeoLocateForm|null $record
     */
    public function setSource(array $request = [], GeoLocateForm $record = null): void
    {
        if (isset($record->source)) {
            $this->source = ($this->expertFileExists && $this->expertReviewExists) ? "reconciled" : "reconcile";

            return;
        }

        $this->source = $request['source'] ?? (($this->expertFileExists && $this->expertReviewExists) ? "reconciled" : "reconcile");
    }

    /**
     * Set vars for if expert review exists.
     *
     * @param \App\Models\Expedition $expedition
     * @return void
     */
    public function setExpertExistVars(Expedition $expedition): void
    {
        $this->expertFileExists = Storage::disk('s3')->exists(config('config.zooniverse_dir.reconciled').'/'.$expedition->id.'.csv');
        $this->expertReviewExists = $expedition->nfnActor->pivot->expert;
    }

    /**
     * Find project header for subjects.
     *
     * @param \App\Models\Expedition $expedition
     * @return array
     */
    private function getCsvHeader(Expedition $expedition): array
    {
        $csvFilePath = $this->source === 'reconcile' ? config('config.zooniverse_dir.reconcile').'/'.$expedition->id.'.csv' : config('config.zooniverse_dir.reconciled').'/'.$expedition->id.'.csv';

        return Cache::remember(md5($this->source), 14440, function () use ($csvFilePath) {
            $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $csvFilePath, 'r');
            $this->awsS3CsvService->createCsvReaderFromStream();
            $this->awsS3CsvService->csv->setHeaderOffset();

            $array = $this->awsS3CsvService->csv->getHeader();

            return array_values(array_filter($array, function ($e) {
                return ($e !== 'subject_id');
            }));
        });
    }

    /**
     * Find GeoLocateForm by id.
     *
     * @param int $id
     * @return GeoLocateForm
     */
    public function findGeoLocateFormById(int $id): GeoLocateForm
    {
        return $this->geoLocateFormRepository->find($id);
    }

    /**
     * Find form with expedition count. Used in Groups for deleting.
     *
     * @param int $formId
     * @return mixed
     */
    public function findGeoLocateFormByIdWithExpeditionCount(int $formId): mixed
    {
        return $this->geoLocateFormRepository->findByIdWithRelationCount($formId, 'expeditions');
    }

    /**
     * Delete all geolocate records for expedition.
     *
     * @param int $expeditionId
     * @return void
     */
    public function deleteGeoLocate(int $expeditionId): void
    {
        $this->geoLocateRepository->getBy('subject_expeditionId', '=', $expeditionId)->each(function ($geoLocate) {
            $geoLocate->delete();
        });
    }

    /**
     * Delete GeoLocate csv file.
     *
     * @param int $expeditionId
     * @return void
     */
    public function deleteGeoLocateFile(int $expeditionId): void
    {
        $filePath = config('config.geolocate_dir.export').'/'.$expeditionId.'.csv';
        if (Storage::disk('s3')->exists($filePath)) {
            Storage::disk('s3')->delete($filePath);
        }
    }
}