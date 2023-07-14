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
    private string $sourceType;

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
     * Get form by expedition id.
     *
     * @param int $expeditionId
     * @return \App\Models\GeoLocateForm|null
     */
    public function getFormByExpeditionId(int $expeditionId): ?GeoLocateForm
    {
        return $this->geoLocateFormRepository->findBy('expedition_id', $expeditionId);
    }

    /**
     * Get the form based on new or existing.
     *
     * @param \App\Models\Expedition $expedition
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getForm(Expedition $expedition): array
    {
        $record = $this->getFormByExpeditionId($expedition->id);

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
        $header = $this->getHeader($expedition);

        return [
            'entries'    => old('entries', 1),
            'sourceType' => $this->sourceType,
            'fields'     => $this->getGeoLocateFields(),
            'header'     => $header,
            'data'       => null,
            'exported'   => !empty($expedition->geoLocateActor->pivot->state)
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
        $header = $this->getHeader($expedition);

        $frmData = null;
        for ($i = 0; $i < $record->properties['entries']; $i++) {
            $frmData[$i] = $record->properties['exportFields'][$i];
        }

        return [
            'entries'    => $record->properties['entries'],
            'sourceType' => $record->properties['sourceType'],
            'fields'     => $this->getGeoLocateFields(),
            'header'     => $header,
            'data'       => $frmData,
            'exported'   => !empty($expedition->geoLocateActor->pivot->state)
        ];
    }

    /**
     * Save the export form data.
     *
     * @param array $fields
     * @param int $expeditionId
     * @return void
     */
    public function saveForm(array $fields, int $expeditionId): void
    {
        $attributes = ['expedition_id' => $expeditionId];
        $values = [
            'expedition_id' => $expeditionId,
            'properties'    => $fields,
        ];

        $this->geoLocateFormRepository->updateOrCreate($attributes, $values);
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
     * Map the posted geolocate form order data.
     *
     * @param array $data
     * @return array
     */
    public function cleanArray(array $data): array
    {
        unset($data['_token']);

        return $data;
    }

    /**
     * Set sourceType by using formData.
     *
     * @param \App\Models\Expedition $expedition
     * @param string|null $sourceType
     */
    public function setSourceType(Expedition $expedition, string $sourceType = null): void
    {
        $this->expertFileExists = Storage::disk('s3')->exists(config('config.zooniverse_dir.reconciled').'/'.$expedition->id.'.csv');
        $this->expertReviewExists = $expedition->nfnActor->pivot->expert;
        $this->sourceType = $sourceType ?? ($this->expertFileExists && $this->expertReviewExists ? "Reconciled Expert Review" : "Reconcile Results");
    }

    /**
     * Return sourceType.
     *
     * @return array
     */
    public function getSourceType(): array
    {
        return [$this->expertFileExists, $this->expertReviewExists, $this->sourceType];
    }

    /**
     * Find project header for subjects.
     *
     * @param \App\Models\Expedition $expedition
     * @return array
     */
    private function getHeader(Expedition $expedition): array
    {
        $csvFilePath = $this->sourceType === 'Reconcile Results' ?
            config('config.zooniverse_dir.reconcile').'/'.$expedition->id.'.csv' :
            config('config.zooniverse_dir.reconciled').'/'.$expedition->id.'.csv';

        return Cache::remember(md5($this->sourceType), 14440, function () use ($csvFilePath) {
            $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $csvFilePath, 'r');
            $this->awsS3CsvService->createCsvReaderFromStream();
            $this->awsS3CsvService->csv->setHeaderOffset();

            $array = $this->awsS3CsvService->csv->getHeader();
            $header[$this->sourceType] = array_filter($array, function ($e) {
                return ($e !== 'subject_id');
            });

            return $header;
        });
    }

    /**
     * Delete all geolocate records for expedition.
     *
     * @param int $expeditionId
     * @return void
     */
    public function deleteGeoLocate(int $expeditionId)
    {
        $this->geoLocateRepository->getBy('subject_expeditionId', '=', $expeditionId)->each(function ($geoLocate) {
            $geoLocate->delete();
        });
    }

    /**
     * Delete GeoLocate csv file.
     *
     * @param string $filePath
     * @return void
     */
    public function deleteGeoLocateFile(string $filePath)
    {
        if (Storage::disk('s3')->exists(config('filesystems.disks.s3.bucket').'/'.$filePath)) {
            Storage::disk('s3')->delete(config('filesystems.disks.s3.bucket').'/'.$filePath);
        }
    }
}