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

use App\Models\RapidRecord;
use App\Services\CsvService;
use App\Services\Export\RapidExportDbService;
use Exception;

/**
 * Class CsvExportType
 *
 * @package App\Services\Export
 */
class GeoLocateExportService
{
    /**
     * @var array
     */
    private $productHeaders;

    /**
     * @var \App\Services\Csv\Csv
     */
    private Csv $csv;

    /**
     * CsvExportType constructor.
     *
     * @param \App\Services\Csv\Csv $csv
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function __construct(Csv $csv)
    {
        $this->productHeaders = json_decode(\File::get(config('config.product_fields_file')), true);
        $this->csv = $csv;
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

        $cursor = $this->rapidExportDbService->getCursorForRapidRecords();

        $first = true;
        foreach ($cursor as $record) {
            $data = $this->setReservedColumns($record, $reservedColumns);

            $csvData = $this->setColumns($record, $fields, $data);

            if (! isset($csvData)) {
                throw new Exception(t('Csv data returned empty while exporting.'));
            }

            if ($first) {
                $this->csvService->insertOne(array_keys($csvData));
                $first = false;
            }

            $this->csvService->insertOne($csvData);
        }

        \File::put($filePath, $this->csvService->writer->toString());
    }

    /**
     * Set reserved columns for adding to doc.
     *
     * @param \App\Models\RapidRecord $record
     * @param array $reservedColumns
     * @return array
     */
    private function setReservedColumns(RapidRecord $record, array $reservedColumns): array
    {
        $data = [];
        foreach ($reservedColumns as $column => $item) {
            $data[$column] = (string) $record->{$item};
        }

        return $data;
    }

    /**
     * Set columns according to export destination.
     *
     * @param \App\Models\RapidRecord $record
     * @param array $fields
     * @param array $data
     * @return array
     */
    public function setColumns(RapidRecord $record, array $fields, array $data): array
    {
        if ($fields['exportDestination'] === 'taxonomic') {
            return $this->setDirectColumns($record, $fields, $data);
        } elseif ($fields['exportDestination'] === 'generic') {
            return $this->setGenericColumns($record, $fields, $data);
        } else {
            return $this->setFormColumns($record, $fields, $data);
        }
    }

    /**
     * Set array for export fields.
     *
     * @param \App\Models\RapidRecord $record
     * @param array $fields
     * @param array $data
     * @return array
     */
    public function setDirectColumns(RapidRecord $record, array $fields, array $data): array
    {
        foreach ($fields['exportFields'] as $field) {
            if (isset($record->{$field})) {
                $data[$field] = $record->{$field};
            }
        }

        return $data;
    }

    /**
     * Set Generic columns.
     *
     * @param \App\Models\RapidRecord $record
     * @param array $fields
     * @param array $data
     * @return array
     */
    public function setGenericColumns(RapidRecord $record, array $fields, array $data): array
    {
        $flattened = collect($fields['exportFields'][0])->forget('order')->flatten();
        $filled = array_fill_keys($flattened->toArray(), '');
        $attributes = $record->getAttributes();
        unset($attributes['_id'], $attributes['updated_at'], $attributes['created_at']);

        return array_merge($data, $filled, $attributes);
    }

    /**
     * Set columns for forms with export fields
     *
     * @param \App\Models\RapidRecord $record
     * @param array $fields
     * @param array $data
     * @return mixed
     */
    public function setFormColumns(RapidRecord $record, array $fields, array $data): array
    {
        foreach ($fields['exportFields'] as $fieldArray) {

            $field = $fieldArray['field'];
            $data[$field] = '';

            // unset to make foreach easier to deal with
            unset($fieldArray['field'], $fieldArray['order']);

            // indexes are the tags. isset skips index values that are null
            foreach ($fieldArray as $index => $value) {
                if (isset($fieldArray[$index]) && (! empty($record->{$value}) || $record->{$value} === "0")) {
                    $data[$field] = $record->{$value};
                    break;
                }
            }
        }

        return $fields['exportDestination'] === 'product' ? $this->setProductHeaders($data) : $data;
    }

    /**
     * Set product headers to proper field names.
     *
     * @param array $data
     * @return array
     */
    private function setProductHeaders(array $data): array
    {
        return collect($data)->mapWithKeys(function($value, $index){
            $key = $index === 'BIOSPEXid' ? 'BIOSPEXid' : $this->productHeaders[$index];
            return [$key => $value];
        })->toArray();
    }
}