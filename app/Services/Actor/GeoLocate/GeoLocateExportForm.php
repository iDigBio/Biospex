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

namespace App\Services\Actor\GeoLocate;

use App\Models\Expedition;
use App\Models\GeoLocateForm;
use App\Repositories\GeoLocateFormRepository;
use App\Repositories\GeoLocateRepository;
use App\Services\Csv\AwsS3CsvService;
use App\Services\Models\ExpeditionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Storage;

class GeoLocateExportForm
{
    private ExpeditionService $expeditionService;

    private GeoLocateFormRepository $geoLocateFormRepository;

    private AwsS3CsvService $awsS3CsvService;

    private GeoLocateRepository $geoLocateRepository;

    private string $source;

    private bool $userReconciledFileExists;

    private bool $expertReconciledFileExists;

    private bool $expertReviewExists;

    /**
     * @var true
     */
    private bool $mismatchSource = false;

    /**
     * Construct.
     */
    public function __construct(
        ExpeditionService $expeditionService,
        GeoLocateFormRepository $geoLocateFormRepository,
        AwsS3CsvService $awsS3CsvService,
        GeoLocateRepository $geoLocateRepository
    ) {

        $this->expeditionService = $expeditionService;
        $this->geoLocateFormRepository = $geoLocateFormRepository;
        $this->awsS3CsvService = $awsS3CsvService;
        $this->geoLocateRepository = $geoLocateRepository;
    }

    /**
     * Find project with relations.
     */
    public function findExpeditionWithRelations(int $expeditionId, array $relations = []): Expedition
    {
        return $this->expeditionService->findExpeditionWithRelations($expeditionId, $relations);
    }

    /**
     * Get the form based on new or existing.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getForm(Expedition $expedition, array $request): array
    {
        $record = isset($request['formId']) ? $this->findGeoLocateFormById($request['formId']) : null;

        $this->setUserReconciledVar($expedition);

        $this->setExpertExistVars($expedition);

        $this->setSource($request, $record);

        return $record === null ? $this->newForm($expedition) : $this->existingForm($record, $expedition);
    }

    /**
     * Return form for selected destination.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function newForm(Expedition $expedition): array
    {
        return [
            'group_id' => $expedition->project->group->id,
            'name' => '',
            'source' => $this->source,
            'entries' => old('entries', 1),
            'fields' => null,
            'user_reconciled' => $this->userReconciledFileExists,
            'expert_reconciled' => $this->expertReconciledFileExists,
            'expert_review' => $this->expertReviewExists,
            'exported' => ! empty($expedition->geoLocateActor->pivot->state),
            'geo' => $this->getGeoLocateFields(),
            'csv' => $this->getCsvHeader($expedition),
            'mismatch_source' => $this->mismatchSource,
            'created_at' => '',
        ];
    }

    /**
     * Return form from existing form.
     *
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
            'group_id' => $record->group_id,
            'name' => $record->name,
            'source' => $this->source,
            'entries' => $entries,
            'fields' => $record->fields,
            'user_reconciled' => $this->userReconciledFileExists,
            'expert_reconciled' => $this->expertReconciledFileExists,
            'expert_review' => $this->expertReviewExists,
            'exported' => ! empty($expedition->geoLocateActor->pivot->state),
            'geo' => $this->getGeoLocateFields(),
            'csv' => $this->getCsvHeader($expedition),
            'mismatch_source' => $this->mismatchSource,
            'created_at' => $record->created_at,
        ];
    }

    /**
     * Save the export form data.
     */
    public function saveForm(array $request, Expedition $expedition): void
    {
        unset($request['_token']);
        $hash = md5(json_encode($request));

        $attributes = [
            'group_id' => $request['group_id'],
            'name' => $request['name'],
            'source' => $request['source'],
            'hash' => $hash,
        ];
        $values = [
            'group_id' => $request['group_id'],
            'name' => $request['name'],
            'source' => $request['source'],
            'hash' => $hash,
            'fields' => $request['fields'],
        ];

        $geoLocateForm = $this->geoLocateFormRepository->updateOrCreate($attributes, $values);
        $expedition->geoLocateForm()->associate($geoLocateForm)->save();
    }

    /**
     * Get GeoLocateExport fields from file.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getGeoLocateFields(): array
    {
        return json_decode(File::get(config('geolocate.fields_file')), true);
    }

    /**
     * Set source by using formData.
     */
    public function setSource(array $request = [], ?GeoLocateForm $record = null): void
    {
        if (isset($record->source)) {
            $this->source = match ($record->source) {
                'reconciled-with-user' => $this->userReconciledFileExists ? 'reconciled-with-user' : 'reconciled',
                'reconciled-with-expert' => ($this->expertReconciledFileExists && $this->expertReviewExists) ? 'reconciled-with-expert' : 'reconciled',
                default => 'reconciled',
            };

            return;
        }

        if (isset($request['source'])) {
            $this->source = $request['source'];

            return;
        }

        $this->source =
            $this->userReconciledFileExists ? 'reconciled-with-user' :
                (($this->expertReconciledFileExists && $this->expertReviewExists) ? 'reconciled-with-expert' : 'reconciled');
    }

    /**
     * Set var for if user reconciled exists.
     */
    public function setUserReconciledVar(Expedition $expedition): void
    {
        $this->userReconciledFileExists = Storage::disk('s3')->exists(config('zooniverse.directory.reconciled-with-user').'/'.$expedition->id.'.csv');
    }

    /**
     * Set vars for if expert review exists.
     */
    public function setExpertExistVars(Expedition $expedition): void
    {
        $this->expertReconciledFileExists = Storage::disk('s3')->exists(config('zooniverse.directory.reconciled-with-expert').'/'.$expedition->id.'.csv');
        $this->expertReviewExists = $expedition->zooniverseActor->pivot->expert;
    }

    /**
     * Find project header for subjects.
     */
    private function getCsvHeader(Expedition $expedition): array
    {
        // Default is reconcile
        $csvFilePath = match ($this->source) {
            'reconciled-with-expert' => config('zooniverse.directory.reconciled-with-expert').'/'.$expedition->id.'.csv',
            'reconciled-with-user' => config('zooniverse.directory.reconciled-with-user').'/'.$expedition->id.'.csv',
            default => config('zooniverse.directory.reconciled').'/'.$expedition->id.'.csv',
        };

        return Cache::remember(md5($this->source), 14440, function () use ($csvFilePath) {
            $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $csvFilePath, 'r');
            $this->awsS3CsvService->createCsvReaderFromStream();
            $this->awsS3CsvService->csv->setHeaderOffset();

            $array = $this->awsS3CsvService->csv->getHeader();

            return array_values(array_filter($array, function ($e) {
                return $e !== 'subject_id';
            }));
        });
    }

    /**
     * Find GeoLocateForm by id.
     */
    public function findGeoLocateFormById(int $id): GeoLocateForm
    {
        return $this->geoLocateFormRepository->find($id);
    }

    /**
     * Find form with expedition count. Used in Groups for deleting.
     */
    public function findGeoLocateFormByIdWithExpeditionCount(int $formId): mixed
    {
        return $this->geoLocateFormRepository->findByIdWithRelationCount($formId, 'expeditions');
    }

    /**
     * Delete all geolocate records for expedition.
     */
    public function deleteGeoLocate(int $expeditionId): void
    {
        $this->geoLocateRepository->getBy('subject_expeditionId', '=', $expeditionId)->each(function ($geoLocate) {
            $geoLocate->delete();
        });
    }

    /**
     * Delete GeoLocateExport csv file.
     */
    public function deleteGeoLocateFile(int $expeditionId): void
    {
        $filePath = config('geolocate.dir.export').'/'.$expeditionId.'.csv';
        if (Storage::disk('s3')->exists($filePath)) {
            Storage::disk('s3')->delete($filePath);
        }
    }
}
