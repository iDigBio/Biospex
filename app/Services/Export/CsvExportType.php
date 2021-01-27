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
use Exception;

/**
 * Class CsvExportType
 *
 * @package App\Services\Export
 */
class CsvExportType
{
    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * @var \App\Services\Export\RapidExportDbService
     */
    private $rapidExportDbService;

    /**
     * CsvExportType constructor.
     *
     * @param \App\Services\Export\RapidExportDbService $rapidExportDbService
     * @param \App\Services\CsvService $csvService
     */
    public function __construct(RapidExportDbService $rapidExportDbService, CsvService $csvService)
    {
        $this->rapidExportDbService = $rapidExportDbService;
        $this->csvService = $csvService;
    }

    /**
     * Build Csv File for export.
     *
     * @param string $filePath
     * @param array $fields
     * @param array $reservedColumns
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    public function build(string $filePath, array $fields, array $reservedColumns)
    {
        $this->csvService->writerCreateFromTempFileObj();
        $this->csvService->writer->addFormatter($this->csvService->setEncoding());

        $cursor = $this->rapidExportDbService->getMongoCursorForRapidRecords();

        $first = true;
        foreach ($cursor as $doc) {
            $data = $this->setReservedColumns($doc, $reservedColumns);

            $csvData = $this->setColumns($doc, $fields, $data);

            if (!isset($csvData)) {
                throw new Exception(t('Csv data returned empty while exporting.'));
            }

            if ($first) {
                $this->csvService->insertOne(array_keys($csvData));
                $first = false;
            }

            $this->csvService->insertOne($csvData);
        }

        \File::put($filePath, $this->csvService->writer->getContent());
    }

    /**
     * Set reserved columns for adding to doc.
     *
     * @param array $doc
     * @param array $reservedColumns
     * @return array
     */
    private function setReservedColumns(array $doc, array $reservedColumns): array
    {
        $data = [];
        foreach ($reservedColumns as $column => $item) {
            $data[$column] = (string) $doc[$item];
        }

        return $data;
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
}