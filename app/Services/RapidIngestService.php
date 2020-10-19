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

use App\Repositories\Interfaces\RapidRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class RapidIngestService extends RapidServiceBase
{
    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * @var \App\Repositories\Interfaces\RapidRecord
     */
    private $rapidInterface;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $csvHeader = [];

    /**
     * @var
     */
    private $rows;

    /**
     * @var int
     */
    private $updatedRecordsCount = 0;

    /**
     * @var \App\Services\RapidFileService
     */
    private $rapidFileService;

    /**
     * RapidIngestService constructor.
     *
     * @param \App\Services\CsvService $csvService
     * @param \App\Repositories\Interfaces\RapidRecord $rapidInterface
     * @param \App\Services\RapidFileService $rapidFileService
     */
    public function __construct(
        CsvService $csvService,
        RapidRecord $rapidInterface,
        RapidFileService $rapidFileService
    )
    {

        $this->csvService = $csvService;
        $this->rapidInterface = $rapidInterface;
        $this->rapidFileService = $rapidFileService;
    }

    /**
     * Process ingest file.
     *
     * @param string $csvFilePath
     * @param bool $import
     * @param \Illuminate\Support\Collection|null $fields
     * @throws \League\Csv\Exception
     */
    public function process(string $csvFilePath, bool $import = false, Collection $fields = null)
    {
        $this->loadCsvFile($csvFilePath);
        $this->setCsvHeader();
        $this->storeHeader();
        $this->setRows();

        $import ? $this->processImportRows() : $this->processUpdateRows($fields);
    }

    /**
     * Unzip file.
     *
     * @param string $filePath
     * @return array|null
     */
    public function unzipFile(string $filePath)
    {
        $importsPath = $this->rapidFileService->getImportsPath();
        $tmpPath = $importsPath . '/tmp';

        Storage::makeDirectory($tmpPath);

        $csvFilePath = null;
        $fileName = null;

        $zipArchive = new ZipArchive();
        $result = $zipArchive->open(Storage::path($filePath));
        if ($result === TRUE) {
            $zipArchive->extractTo(Storage::path($tmpPath));
            $zipArchive->close();

            $files = \File::allFiles(Storage::path($tmpPath));
            foreach ($files as $file) {
                if($file->getExtension() === 'csv') {
                    $fileName = date('d-m-Y_H-i-s') . '_' . $file->getFilename();
                    $csvFilePath = Storage::path($importsPath . '/' . $fileName);
                    \File::move($file->getPathname(), $csvFilePath);
                    break;
                }
            }

            Storage::deleteDirectory($tmpPath);
            Storage::delete($filePath);
        }

        return isset($csvFilePath) ? [$fileName, $csvFilePath] : null;
    }

    /**
     * Load csv file.
     *
     * @param $file
     * @throws \League\Csv\Exception
     */
    public function loadCsvFile($file)
    {
        $this->csvService->readerCreateFromPath($file);
        $this->csvService->setDelimiter();
        $this->csvService->setEnclosure();
        $this->csvService->setHeaderOffset();
    }

    /**
     * Set the header.
     *
     * @return array
     */
    public function setCsvHeader(): array
    {
        return $this->csvHeader = $this->csvService->getHeader();
    }

    /**
     * Store the header file.
     */
    public function storeHeader()
    {
        $this->rapidFileService->storeHeader($this->csvHeader);
    }

    /**
     * Update the header with new values if they are different.
     */
    public function updateHeader()
    {
        $this->rapidFileService->updateHeader($this->csvHeader);
    }

    /**
     * Return column tags.
     *
     * @return array|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application
     */
    public function getColumnTags()
    {
        return $this->rapidFileService->getColumnTags();
    }

    /**
     * Set rows.
     */
    public function setRows()
    {
        $this->rows = $this->csvService->getRecords($this->csvHeader);
    }

    /**
     * Process rows when importing for first time.
     */
    public function processImportRows()
    {
        foreach ($this->rows as $offset => $row) {
            $this->createRecord($row);
        }
    }

    /**
     * Create rapid record.
     *
     * @param $row
     */
    private function createRecord($row)
    {
        if ($this->validateRow($row)) {
            return;
        }

        $this->rapidInterface->create($row);
    }

    /**
     * Validate imports being created.
     *
     * @param $row
     * @return mixed
     */
    public function validateRow($row)
    {
        $attributes = [];
        $validationFields = $this->rapidFileService->getValidationFields();

        foreach ($validationFields as $field) {
            $attributes[$field] = $row[$field];
        }

        $count = $this->rapidInterface->validateRecord($attributes);

        if ($count) {
            $this->errors[] = $row;
        }

        return $count;
    }

    /**
     * Update record using selected fields only.
     * @param \Illuminate\Support\Collection $fields
     */
    public function processUpdateRows(Collection $fields)
    {
        foreach ($this->rows as $offset => $row) {
            $intersect = collect($row)->intersectByKeys($fields->flip())->toArray();

            $this->updateRecord($intersect, $row['_id']);
        }
    }

    /**
     * Update Rapid record.
     *
     * @param array $attributes
     * @param string $id
     */
    public function updateRecord(array $attributes, string $id)
    {
        $result =$this->rapidInterface->update($attributes, $id);

        if (! $result) {
            $this->errors[] = array_merge(['_id' => $id], $attributes);
        }

        $this->updatedRecordsCount++;
    }

    /**
     * Create csv file.
     *
     * @return string
     * @throws \League\Csv\CannotInsertRecord
     */
    public function createCsv() {
        $errors = collect($this->errors)->recursive();
        $header = $errors->first()->keys();

        $fileName = Str::random() . '.csv';
        $filePath = Storage::path(config('config.reports_dir') . '/' . $fileName);

        $this->csvService->writerCreateFromPath($filePath);
        $this->csvService->insertOne($header);
        $this->csvService->insertAll($errors->toArray());


        return route('admin.download.report', ['fileName' => base64_encode($fileName)]);
    }

    /**
     * Get errors
     *
     * @return bool
     */
    public function checkErrors()
    {
        return ! empty($this->errors);
    }

    /**
     * Return the count of updated records.
     *
     * @return int
     */
    public function getUpdatedRecordsCount()
    {
        return $this->updatedRecordsCount;
    }
}