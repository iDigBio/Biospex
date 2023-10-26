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

use App\Models\Expedition;
use App\Models\GeoLocateExport;
use App\Models\GeoLocateForm;
use App\Repositories\DownloadRepository;
use App\Repositories\ExpeditionRepository;
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
    private string $csvFilePath;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepository;

    /**
     * Construct
     *
     * @param \App\Services\Csv\AwsS3CsvService $awsS3CsvService
     * @param \App\Repositories\GeoLocateRepository $geoLocateRepository
     */
    public function __construct(
        AwsS3CsvService $awsS3CsvService,
        GeoLocateRepository $geoLocateRepository,
        ExpeditionRepository $expeditionRepository,
        DownloadRepository $downloadRepository
    )
    {
        $this->awsS3CsvService = $awsS3CsvService;
        $this->geoLocateRepository = $geoLocateRepository;
        $this->expeditionRepository = $expeditionRepository;
        $this->downloadRepository = $downloadRepository;
    }

    /**
     * Migrate records to MongoDb.
     *
     * @param \App\Models\Expedition $expedition
     * @return void
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
            $this->geoLocateRepository->updateOrCreate(['subject_id' => (int)$record['subject_id']], $record);
        }

        $this->awsS3CsvService->closeBucketStream();
    }

    /**
     * Set file source path according to source type.
     *
     * @param \App\Models\Expedition $expedition
     * @return string
     */
    private function setSourceFile(Expedition $expedition): string
    {
        return config('config.zooniverse_dir.' . $expedition->geoLocateForm->source) . '/' . $expedition->id . '.csv';
    }

    /**
     * Set csv file paths.
     *
     * @param int $expeditionId
     * @return void
     */
    public function setCsvFilePath(int $expeditionId): void
    {
        $this->csvFilePath = config('config.geolocate.dir.export').'/'.$expeditionId.'.csv';
    }

    /**
     * Get CSV file path.
     *
     * @return string
     */
    public function getCsvFilePath(): string
    {
        return $this->csvFilePath;
    }

    /**
     * Build Csv File for export inside efs directory.
     *
     * @param \App\Models\Expedition $expedition
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     */
    public function build(Expedition $expedition): void
    {
        $this->awsS3CsvService->csv->writerCreateFromPath(Storage::disk('efs')->path($this->csvFilePath));

        $cursor = $this->geoLocateRepository->getByExpeditionId($expedition->id);

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
     *
     * @param \App\Models\GeoLocateExport $record
     * @param \App\Models\GeoLocateForm $form
     * @return array
     */
    public function setDataArray(GeoLocateExport $record, GeoLocateForm $form): array
    {
        $data = collect($form->fields)->mapWithKeys(function (array $field) use($record) {
            return [$field['geo'] => $record->{$field['csv']}];
        })->toArray();

        $data['CatalogNumber'] = $record->_id;

        return $data;
    }

    /**
     * Move csv file to s3
     *
     * @return string
     * @throws \Exception
     */
    public function moveCsvFile(): string
    {
        if (Storage::disk('efs')->exists($this->csvFilePath)) {
            Storage::disk('s3')->writeStream($this->csvFilePath, Storage::disk('efs')->readStream($this->csvFilePath));

            if (!Storage::disk('s3')->exists($this->csvFilePath)) {
                throw new Exception(t('Could not move csv to AWS storage: %s', $this->csvFilePath));
            }

            Storage::disk('efs')->delete($this->csvFilePath);
        }

        return $this->csvFilePath;
    }

    /**
     * Create or update download file.
     *
     * @param \App\Models\Expedition $expedition
     * @return void
     */
    public function createDownload(Expedition $expedition): void
    {
        $values = [
            'expedition_id' => $expedition->id,
            'actor_id'      => config('config.geolocate.actor_id'),
            'file'          => $expedition->id . '.csv',
            'type'          => 'export',
        ];
        $attributes = [
            'expedition_id' => $expedition->id,
            'actor_id'      => config('config.geolocate.actor_id'),
            'file'          => $expedition->id . '.csv',
            'type'          => 'export',
        ];

        $this->downloadRepository->updateOrCreate($attributes, $values);
    }

    /**
     * Update actor_expedition pivot state.
     *
     * @param \App\Models\Expedition $expedition
     * @param int $state
     * @return void
     */
    public function updateState(Expedition $expedition, int $state): void
    {
        $expedition->actors()->updateExistingPivot(config('config.geolocate.actor_id'), [
            'state' => $state,
        ]);
    }
}