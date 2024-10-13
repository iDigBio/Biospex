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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Actor\GeoLocate;

use App\Models\Download;
use App\Models\Expedition;
use App\Models\GeoLocateExport;
use App\Models\GeoLocateForm;
use App\Services\Csv\AwsS3CsvService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class CsvExportType
 */
class GeoLocateExportService
{
    private string $csvFilePath;

    /**
     * Construct
     */
    public function __construct(
        protected GeoLocateExport $geoLocateExport,
        protected AwsS3CsvService $awsS3CsvService,
        protected Download $download,
    ) {}

    /**
     * Process GeoLocate export.
     *
     * @throws \Exception
     */
    public function process(Expedition $expedition): void
    {
        $this->setCsvFilePath($expedition->id);

        try {
            $this->migrateRecords($expedition);
            $this->build($expedition);
            $this->moveCsvFile();
            $this->createDownload($expedition);
            $this->updateActorExpeditionPivot($expedition);

        } catch (Throwable $throwable) {
            $expedition->actors()->updateExistingPivot(config('geolocate.actor_id'), [
                'state' => 0,
            ]);

            $csvFilePath = $this->getCsvFilePath();

            if (Storage::disk('s3')->exists($this->csvFilePath)) {
                Storage::disk('s3')->delete($this->csvFilePath);
            }

            if (Storage::disk('efs')->exists($csvFilePath)) {
                Storage::disk('efs')->delete($csvFilePath);
            }

            throw new Exception(t('Could not export GeoLocate data for Expedition %s', $expedition->title));
        }
    }

    /**
     * Migrate records to MongoDb.
     *
     * @throws \League\Csv\Exception
     */
    public function migrateRecords(Expedition $expedition): void
    {

        $sourceFile = $this->setSourceFile($expedition);

        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $sourceFile, 'r');
        $this->awsS3CsvService->createCsvReaderFromStream();
        $this->awsS3CsvService->csv->setHeaderOffset();
        $header = $this->awsS3CsvService->csv->getHeader();
        $records = $this->awsS3CsvService->csv->getRecords($header);

        foreach ($records as $record) {
            $this->geoLocateExport->updateOrCreate(['subject_id' => (int) $record['subject_id']], $record);
        }

        $this->awsS3CsvService->closeBucketStream();
    }

    /**
     * Set file source path according to source type.
     */
    private function setSourceFile(Expedition $expedition): string
    {
        return config('zooniverse.directory.'.$expedition->geoLocateForm->source).'/'.$expedition->id.'.csv';
    }

    /**
     * Set csv file paths.
     */
    public function setCsvFilePath(int $expeditionId): void
    {
        $this->csvFilePath = config('geolocate.dir.export').'/'.$expeditionId.'.csv';
    }

    /**
     * Get CSV file path.
     */
    public function getCsvFilePath(): string
    {
        return $this->csvFilePath;
    }

    /**
     * Build Csv File for export inside efs directory.
     *
     * @throws \League\Csv\CannotInsertRecord
     * @throws \Exception
     */
    public function build(Expedition $expedition): void
    {
        $this->awsS3CsvService->csv->writerCreateFromPath(Storage::disk('efs')->path($this->csvFilePath));

        $cursor = $this->geoLocateExport->where('subject_expeditionId', $expedition->id)
            ->options(['allowDiskUse' => true])->timeout(86400)->cursor();

        $first = true;
        foreach ($cursor as $record) {

            $csvData = $this->setDataArray($record, $expedition->geoLocateForm);

            if (! isset($csvData)) {
                throw new Exception(t('Csv data returned empty while exporting.'));
            }

            if ($first) {
                $this->awsS3CsvService->csv->insertOne(array_keys($csvData));
                $first = false;
            }

            $this->awsS3CsvService->csv->insertOne($csvData);
        }
    }

    /**
     * Set array for export fields.
     */
    public function setDataArray(GeoLocateExport $record, GeoLocateForm $form): array
    {
        $data = collect($form->fields)->mapWithKeys(function (array $field) use ($record) {
            return [$field['geo'] => $record->{$field['csv']}];
        })->toArray();

        $data['CatalogNumber'] = $record->_id;

        return $data;
    }

    /**
     * Move csv file to s3
     *
     * @throws \Exception
     */
    public function moveCsvFile(): void
    {
        if (Storage::disk('efs')->exists($this->csvFilePath)) {
            Storage::disk('s3')->writeStream($this->csvFilePath, Storage::disk('efs')->readStream($this->csvFilePath));

            if (! Storage::disk('s3')->exists($this->csvFilePath)) {
                throw new Exception(t('Could not move csv to AWS storage: %s', $this->csvFilePath));
            }

            Storage::disk('efs')->delete($this->csvFilePath);
        }
    }

    /**
     * Create or update download file.
     */
    public function createDownload(Expedition $expedition): void
    {
        $values = [
            'expedition_id' => $expedition->id,
            'actor_id' => config('geolocate.actor_id'),
            'file' => $expedition->id.'.csv',
            'type' => 'export',
        ];
        $attributes = [
            'expedition_id' => $expedition->id,
            'actor_id' => config('geolocate.actor_id'),
            'file' => $expedition->id.'.csv',
            'type' => 'export',
        ];

        $this->download->updateOrCreate($attributes, $values);
    }

    /**
     * Update actor expedition pivot.
     */
    public function updateActorExpeditionPivot(Expedition $expedition): void
    {
        $expedition->actors()->updateExistingPivot(config('geolocate.actor_id'), [
            'state' => 1,
        ]);
    }

    /**
     * Delete GeoLocateForm and associated data and file.
     */
    public function destroyGeoLocate(Expedition &$expedition): void
    {
        $this->deleteGeoLocateFile($expedition->id);
        $this->deleteGeoLocateRecords($expedition->id);

        $expedition->geoLocateForm()->dissociate()->save();
        $expedition->actors()->updateExistingPivot(config('geolocate.actor_id'), [
            'state' => 0,
        ]);
    }

    /**
     * Delete all geolocate records for expedition.
     */
    public function deleteGeoLocateRecords(int $expeditionId): void
    {
        $this->geoLocateExport->where('subject_expeditionId', '=', $expeditionId)->get()->each(function ($geoLocate) {
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
