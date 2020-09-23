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

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;

class GeoLocateExportService
{
    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * GeoLocateExportService constructor.
     *
     * @param \App\Services\MongoDbService $mongoDbService
     * @param \App\Services\CsvService $csvService
     */
    public function __construct(MongoDbService $mongoDbService, CsvService $csvService)
    {
        $this->mongoDbService = $mongoDbService;
        $this->csvService = $csvService;
    }

    /**
     * Determine export type and process.
     *
     * @param array $fields
     * @return string|null
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    public function buildGeoLocateExport(array $fields)
    {
        if ($fields['exportType'] === 'csv') {
            $csvData = $this->buildCsvData($fields);
            if (!isset($csvData[0])) {
                throw new Exception(t('Csv data returned empty while exporting for GEOLocate'));
            }

            return $this->buildCsvFile($csvData, $fields['frmName']);
        }

        return null;
    }

    /**
     * Get cursor for rapid documents
     * @return mixed
     */
    public function getRapidRecordsCursor()
    {
        $this->mongoDbService->setCollection('rapid_records');

        $cursor = $this->mongoDbService->find();
        $cursor->setTypeMap([
            'array'    => 'array',
            'document' => 'array',
            'root'     => 'array',
        ]);

        return $cursor;
    }


    /**
     * Build csv data for GeoLocate export.
     *
     * @param array $fields
     * @return array
     */
    public function buildCsvData(array $fields): array
    {
        $cursor = $this->getRapidRecordsCursor();

        $csvData = [];

        foreach ($cursor as $doc) {
            $csvData[] = $this->setColumnData($doc, $fields);
        }

        return $csvData;
    }

    /**
     * Set column headers and data according to what was selected.
     *
     * @param $doc
     * @param $fields
     * @return mixed
     */
    public function setColumnData($doc, $fields)
    {
        $data['CatalogNumber'] = (string) $doc['_id'];

        foreach ($fields['exportFields'] as $fieldArray) {
            $field = $fieldArray['field'];
            $data[$field] = '';

            // unset to make foreach easier to deal with
            unset($fieldArray['field'], $fieldArray['order']);

            // indexes are the tags. isset skips index values that are null
            foreach ($fieldArray as $index => $value) {
                if (isset($fieldArray[$index]) && !empty($doc[$value])) {
                    $data[$field] = $doc[$value];
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Build csv file and return it.
     *
     * @param array $csvData
     * @param string $frmName
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function buildCsvFile(array $csvData, string $frmName): string
    {
        $header = array_keys($csvData[0]);

        $fileName = $frmName . '.csv';
        $file = Storage::path(config('config.rapid_export_dir').'/'.$fileName);
        $this->csvService->writerCreateFromPath($file);
        $this->csvService->writer->addFormatter($this->csvService->setEncoding());

        $this->csvService->insertOne($header);
        $this->csvService->insertAll($csvData);

        return route('admin.download.export', ['fileName' => $fileName]);
    }
}