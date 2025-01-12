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
use App\Models\GeoLocateExport;
use App\Services\Csv\AwsS3CsvService;

/**
 * Constructor method for GeoLocateResultCsvService.
 *
 * @param  AwsS3CsvService  $awsS3CsvService  Service for handling AWS S3 interactions and CSV operations.
 * @param  GeoLocateExport  $geoLocateExport  Model for interacting with GeoLocateExport records.
 * @param  Download  $download  Model for managing download records.
 */
class GeoLocateResultCsvService
{
    public function __construct(
        protected AwsS3CsvService $awsS3CsvService,
        protected GeoLocateExport $geoLocateExport,
        protected Download $download
    ) {}

    /**
     * Processes the download of a CSV file from an S3 bucket, cleans the header,
     * and processes the records in the CSV file.
     *
     * @param  string  $sourceFile  The name of the CSV file to be downloaded from the S3 bucket.
     * @param  array  $fields  The list of fields to be used to clean and map the header of the CSV file.
     *
     * @throws \League\Csv\Exception
     * @throws \League\Csv\SyntaxError
     */
    public function processCsvDownload(string $sourceFile, array $fields): void
    {
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $sourceFile, 'r');
        $this->awsS3CsvService->createCsvReaderFromStream();
        $this->awsS3CsvService->csv->setHeaderOffset();
        $header = $this->awsS3CsvService->csv->getHeader();
        $cleanHeader = $this->cleanHeader($header, $fields);
        $this->processRecords($cleanHeader);
        $this->awsS3CsvService->closeBucketStream();
    }

    /**
     * Creates or updates a geo-reconciled data file in a specified destination.
     * The method generates a CSV file from geo-located export data filtered by the expedition ID
     * and uploads it to an AWS S3 bucket.
     *
     * @param  string  $destinationFile  The name of the file to be created or updated.
     * @param  int  $expeditionId  The ID of the expedition to filter the geo-located export data.
     *
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function createUpdateGeoReconciledDownload(string $destinationFile, int $expeditionId): void
    {
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $destinationFile, 'w');
        $this->awsS3CsvService->createCsvWriterFromStream();

        $counter = 0;
        foreach ($this->geoLocateExport->where('subject_expeditionId', $expeditionId)->lazy() as $record) {
            unset($record->id, $record->updated_at, $record->created_at);
            if ($counter === 0) {
                $this->awsS3CsvService->csv->insertOne(array_keys($record->toArray()));
                $counter++;

                continue;
            }
            $this->awsS3CsvService->csv->insertOne($record->toArray());
        }

        $this->awsS3CsvService->closeBucketStream();
    }

    /**
     * Cleans the provided CSV header by removing fields that match the given set of fields.
     * The method filters out any header elements that exist in the flattened fields array.
     *
     * @param  array  $csvHeader  The original header of the CSV file as an array.
     * @param  array  $fields  The fields to be removed from the CSV header.
     * @return array The cleaned CSV header with specified fields removed.
     */
    public function cleanHeader(array $csvHeader, array $fields): array
    {
        $flatFields = collect($fields)->flatten()->toArray();

        return collect($csvHeader)->filter(function ($item) use ($flatFields) {
            return ! in_array($item, $flatFields);
        })->toArray();
    }

    /**
     * Processes the records retrieved from the CSV, finds the corresponding geo-location export record based on the catalog number,
     * updates the found record with the remaining data, and removes the catalog number from the data array.
     *
     * @param  array  $cleanHeader  The cleaned header array used during the processing of records.
     *
     * @throws \League\Csv\Exception
     */
    public function processRecords(array $cleanHeader): void
    {
        $records = $this->awsS3CsvService->csv->getRecords();
        foreach ($records as $record) {
            $geoLocateExport = $this->geoLocateExport->find($record['CatalogNumber']);
            unset($record['CatalogNumber']);
            $geoLocateExport->update($record);
        }
    }

    /**
     * Creates or updates a download record associated with the specified expedition ID.
     *
     * @param  int  $expeditionId  The identifier of the expedition for which the download record is being created or updated.
     */
    public function createOrUpdateDownload(int $expeditionId): void
    {
        $this->download->updateOrCreate([
            'expedition_id' => $expeditionId,
            'actor_id' => config('geolocate.actor_id'),
            'file' => $expeditionId.'.csv',
            'type' => 'geo-reconciled',
        ], [
            'expedition_id' => $expeditionId,
            'actor_id' => config('geolocate.actor_id'),
            'file' => $expeditionId.'.csv',
            'type' => 'geo-reconciled',
        ]);
    }
}
