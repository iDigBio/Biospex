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

namespace App\Services\Export;

use App\Services\CsvService;
use App\Services\MongoDbService;
use Exception;
use Illuminate\Support\Facades\Storage;

/**
 * Class CsvExportType
 *
 * @package App\Services\Export
 */
class CsvExportType
{
    /**
     * @var \App\Services\MongoDbService
     */
    private $mongoDbService;

    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    private $reservedColumns;

    /**
     * CsvExportType constructor.
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
     * Start building csv data and return file route.
     *
     * @param array $fields
     * @param array $reservedColumns
     * @return string
     * @throws \Exception
     */
    public function build(array $fields, array $reservedColumns): string
    {
        $this->reservedColumns = $reservedColumns;

        $csvData = $this->buildCsvData($fields);
        if (! isset($csvData[0])) {
            throw new Exception(t('Csv data returned empty while exporting.'));
        }

        return $this->buildCsvFile($csvData, $fields['frmFile']);
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
            $data = $this->buildReservedColumns($doc);
            $csvData[] = $this->setColumns($doc, $fields, $data);
        }

        return $csvData;
    }

    /**
     * Build csv file and return it.
     *
     * @param array $csvData
     * @param string $frmFile
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function buildCsvFile(array $csvData, string $frmFile): string
    {
        $header = array_keys($csvData[0]);

        $file = Storage::path(config('config.rapid_export_dir').'/'.$frmFile);
        $this->csvService->writerCreateFromPath($file);
        $this->csvService->writer->addFormatter($this->csvService->setEncoding());

        $this->csvService->insertOne($header);
        $this->csvService->insertAll($csvData);

        return route('admin.download.export', ['file' => base64_encode($frmFile)]);
    }

    /**
     * Get cursor for rapid documents
     *
     * @return mixed
     */
    public function getRapidRecordsCursor()
    {
        $this->mongoDbService->setCollection('rapid_records');

        $cursor = $this->mongoDbService->find([], ['batchSize' => 100]);
        $cursor->setTypeMap([
            'array'    => 'array',
            'document' => 'array',
            'root'     => 'array',
        ]);

        return $cursor;
    }

    /**
     * Set columns according to export destination.
     *
     * @param $doc
     * @param array $fields
     * @param array $data
     * @return array
     */
    public function setColumns($doc, array $fields, array $data): array
    {
        if($fields['exportDestination'] === 'taxonomic'){
            return $this->setDirectColumns($doc, $fields, $data);
        }elseif ($fields['exportDestination'] === 'generic') {
            return $this->setGenericColumns($doc, $fields, $data);
        }else {
            return $this->setFormColumns($doc, $fields, $data);
        }
    }

    /**
     * Set array for export fields.
     *
     * @param $doc
     * @param array $fields
     * @param array $data
     * @return array
     */
    public function setDirectColumns($doc, array $fields, array $data): array
    {
        foreach ($fields['exportFields'] as $field) {
            if (isset($doc[$field])) {
                $data[$field] = $doc[$field];
            }
        }

        return $data;
    }

    /**
     * Set Generic columns.
     *
     * @param $doc
     * @param array $fields
     * @param array $data
     * @return array
     */
    public function setGenericColumns($doc, array $fields, array $data): array
    {
        $flattened = collect($fields['exportFields'][0])->forget('order')->flatten()->flip();

        return array_merge($data, collect($doc)->intersectByKeys($flattened)->toArray());
    }

    /**
     * Set columns for forms with export fields
     *
     * @param $doc
     * @param array $fields
     * @param array $data
     * @return mixed
     */
    public function setFormColumns($doc, array $fields, array $data): array
    {
        foreach ($fields['exportFields'] as $fieldArray) {

            $field = $fieldArray['field'];
            $data[$field] = '';

            // unset to make foreach easier to deal with
            unset($fieldArray['field'], $fieldArray['order']);

            // indexes are the tags. isset skips index values that are null
            foreach ($fieldArray as $index => $value) {
                if (isset($fieldArray[$index]) && ! empty($doc[$value])) {
                    $data[$field] = $doc[$value];
                    break;
                }
            }
        }

        return $data;
    }

    /**
     * Set reserved columns.
     *
     * @param $doc
     * @return array
     */
    public function buildReservedColumns($doc): array
    {
        $data = [];
        foreach ($this->reservedColumns as $column => $item) {
            $data[$column] = (string) $doc[$item];
        }

        return $data;
    }
}