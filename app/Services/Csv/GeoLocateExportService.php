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

namespace App\Services\Csv;

use App\Models\GeoLocate;
use App\Models\GeoLocateForm;
use App\Repositories\GeoLocateRepository;
use Exception;
use Illuminate\Support\Facades\Storage;

/**
 * Class CsvExportType
 *
 * @package App\Services\Export
 */
class GeoLocateExportService
{

    /**
     * @var \App\Services\Csv\AwsS3CsvService
     */
    private AwsS3CsvService $awsS3CsvService;

    /**
     * @var \App\Repositories\GeoLocateRepository
     */
    private GeoLocateRepository $geoLocateRepository;

    /**
     * @var string
     */
    private string $sourceType;

    /**
     * Construct
     *
     * @param \App\Services\Csv\AwsS3CsvService $awsS3CsvService
     * @param \App\Repositories\GeoLocateRepository $geoLocateRepository
     */
    public function __construct(AwsS3CsvService $awsS3CsvService, GeoLocateRepository $geoLocateRepository)
    {
        $this->awsS3CsvService = $awsS3CsvService;
        $this->geoLocateRepository = $geoLocateRepository;
    }

    /**
     * Migrate records to MongoDb.
     *
     * @param \App\Models\GeoLocateForm $form
     * @return void
     * @throws \League\Csv\Exception
     */
    public function migrateRecords(GeoLocateForm $form): void
    {

        $sourceFile = $this->setSourceFile($form);

        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $sourceFile, 'r');
        $this->awsS3CsvService->createCsvReaderFromStream();
        $this->awsS3CsvService->csv->setHeaderOffset();
        $header = $this->awsS3CsvService->csv->getHeader();
        $records = $this->awsS3CsvService->csv->getRecords($header);

        foreach ($records as $record) {
            $this->geoLocateRepository->updateOrCreate(['subject_id' => $record['subject_id']], $record);
        }

        $this->awsS3CsvService->closeBucketStream();
    }

    /**
     * Set the source type.
     * Reconciled Expert Review or Reconcile Results
     *
     * @param \App\Models\GeoLocateForm $form
     * @return void
     */
    public function setSourceType(GeoLocateForm $form): void
    {
        $this->sourceType = $form->properties['sourceType'];
    }

    /**
     * Set file source path according to source type.
     *
     * @param \App\Models\GeoLocateForm $form
     * @return string
     */
    private function setSourceFile(GeoLocateForm $form): string
    {
        return $this->sourceType === "Reconciled Expert Review" ?
            config('config.zooniverse_dir.reconciled') . '/' . $form->expedition_id . '.csv':
            config('config.zooniverse_dir.reconcile') . '/' . $form->expedition_id . '.csv';
    }

    /**
     * Build Csv File for export inside efs directory.
     *
     * @param \App\Models\GeoLocateForm $form
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function build(GeoLocateForm $form): void
    {
        $efsCsvFilePath = Storage::disk('efs')->path($form->file_path);
        $this->awsS3CsvService->csv->writerCreateFromPath($efsCsvFilePath);

        $cursor = $this->geoLocateRepository->getByExpeditionId($form->expedition_id);

        $first = true;
        foreach ($cursor as $record) {
            $data = ['CatalogNumber' => $record->_id];

            $csvData = $this->setDataArray($record, $form, $data);

            if (! isset($csvData)) {
                throw new Exception(t('Csv data returned empty while exporting.'));
            }

            if ($first) {
                $this->awsS3CsvService->csv->insertOne(array_keys($csvData));
                $first = false;
            }

            $this->awsS3CsvService->csv->insertOne($csvData);
        }

        if (!Storage::disk('efs')->exists($form->file_path)) {
            throw new Exception(t('Csv export file is missing: %s', $form->file_path));
        }
    }

    /**
     * Set array for export fields.
     *
     * @param \App\Models\GeoLocate $record
     * @param \App\Models\GeoLocateForm $form
     * @param array $data
     * @return array
     */
    public function setDataArray(GeoLocate $record, GeoLocateForm $form, array $data): array
    {
        foreach ($form->properties['exportFields'] as $fieldArray) {

            $field = $fieldArray['field'];
            $data[$field] = '';

            // unset to make foreach easier to deal with
            unset($fieldArray['field']);

            // indexes are the tags. isset skips index values that are null
            foreach ($fieldArray as $value) {
                $data[$field] = $record->{$value};
                break;
            }
        }

        return $data;
    }

    /**
     * Move csv file to s3
     *
     * @param \App\Models\GeoLocateForm $form
     * @return void
     * @throws \Exception
     */
    public function moveCsvFile(GeoLocateForm $form): void
    {
        if (Storage::disk('efs')->exists($form->file_path)) {
            Storage::disk('s3')->writeStream($form->file_path, Storage::disk('efs')->readStream($form->file_path));

            if (!Storage::disk('s3')->exists($form->file_path)) {
                throw new Exception(t('Could not move csv to AWS storage: %s', $form->file_path));
            }

            Storage::disk('efs')->delete($form->file_path);
        }
    }
}