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

namespace App\Services;

use App\Models\RapidHeader;
use App\Services\Model\RapidHeaderModelService;
use App\Services\Model\RapidRecordModelService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Class RapidIngestService
 *
 * @package App\Services
 */
class RapidIngestService extends RapidServiceBase
{
    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * @var \App\Services\Model\RapidRecordModelService
     */
    private $rapidRecordModelService;

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
     * @var
     */
    private $updateFields;

    /**
     * @var int
     */
    private $updatedRecordsCount = 0;

    /**
     * @var \App\Services\Model\RapidHeaderModelService
     */
    private $rapidHeaderModelService;

    /**
     * RapidIngestService constructor.
     *
     * @param \App\Services\CsvService $csvService
     * @param \App\Services\Model\RapidRecordModelService $rapidRecordModelService
     * @param \App\Services\Model\RapidHeaderModelService $rapidHeaderModelService
     */
    public function __construct(
        CsvService $csvService,
        RapidRecordModelService $rapidRecordModelService,
        RapidHeaderModelService $rapidHeaderModelService
    ) {

        $this->csvService = $csvService;
        $this->rapidRecordModelService = $rapidRecordModelService;
        $this->rapidHeaderModelService = $rapidHeaderModelService;
    }

    /**
     * Process ingest file.
     *
     * @param string $csvFilePath
     * @param bool $import
     * @return \App\Models\RapidHeader
     * @throws \League\Csv\Exception
     */
    public function process(string $csvFilePath, bool $import = false): RapidHeader
    {
        $this->loadCsvFile($csvFilePath);
        $this->setCsvHeader();
        $this->setRows();

        $import ? $this->processImportRows() : $this->processUpdateRows();

        return $this->storeHeader();
    }

    /**
     * Unzip file.
     *
     * @param string $filePath
     * @return string|null
     */
    public function unzipFile(string $filePath): ?string
    {
        $importsPath = $this->getImportsPath();
        $tmpPath = $this->getImportsTmpPath();

        Storage::makeDirectory($tmpPath);

        $fileName = null;

        $zipArchive = new ZipArchive();
        $result = $zipArchive->open(Storage::path($filePath));
        if ($result === true) {
            $zipArchive->extractTo(Storage::path($tmpPath));
            $zipArchive->close();

            $files = \File::allFiles(Storage::path($tmpPath));
            foreach ($files as $file) {
                if ($file->getExtension() === 'csv') {
                    $fileName = $file->getFilename();
                    \File::move($file->getPathname(), Storage::path($importsPath.'/'.$fileName));
                    break;
                }
            }

            Storage::deleteDirectory($tmpPath);
            Storage::delete($filePath);
        }

        return $fileName;
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
     */
    public function setCsvHeader()
    {
        $this->csvHeader = $this->csvService->getHeader();
    }

    /**
     * Update and store new header.
     *
     * @return \App\Models\RapidHeader
     */
    public function storeHeader(): RapidHeader
    {
        $header = $this->rapidHeaderModelService->getLatestHeader();
        $headerArray = isset($header->data) ? $header->data : [];
        $protectedFields = $this->getProtectedFields();

        $diff = collect($this->csvHeader)->diff($headerArray)->reject(function ($field) use ($protectedFields) {
            return in_array($field, $protectedFields);
        });

        $rapidHeader = collect($headerArray)->concat($diff)->toArray();

        return $this->rapidHeaderModelService->create(['data' => $rapidHeader]);
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

        $this->rapidRecordModelService->create($row);
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
        $validationFields = $this->getValidationFields();

        foreach ($validationFields as $field) {
            $attributes[$field] = $row[$field];
        }

        $count = $this->rapidRecordModelService->validateRecord($attributes);

        if ($count) {
            $this->errors[] = $row;
        }

        return $count;
    }

    /**
     * Update record using selected fields only.
     */
    public function processUpdateRows()
    {
        $this->setUpdateFields();

        foreach ($this->rows as $offset => $row) {
            $id = $row['_id'];
            if ($id === null) {
                $this->errors[] = $row;
                continue;
            }

            $intersect = collect($row)->intersectByKeys($this->updateFields)->toArray();

            $this->updateRecord($intersect, $id);
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
        $result = $this->rapidRecordModelService->update($attributes, $id);

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
    public function createCsv(): string
    {
        $errors = collect($this->errors)->recursive();
        $header = $errors->first()->keys();

        $fileName = Str::random().'.csv';
        $filePath = Storage::path(config('config.reports_dir').'/'.$fileName);

        $this->csvService->writerCreateFromPath($filePath);
        $this->csvService->insertOne($header->toArray());
        $this->csvService->insertAll($errors->toArray());

        return route('admin.download.report', ['file' => base64_encode($fileName)]);
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

    /**
     * Set fields to be updated.
     */
    private function setUpdateFields()
    {
        $this->updateFields = collect($this->csvHeader)->flip()->filter(function ($field, $key) {
            return strpos($key, '_rapid') !== false;
        })->toArray();
    }

    /**
     * Return updated fields for sending in update notification.
     *
     * @return array
     */
    public function getUpdatedFields(): array
    {
        return array_keys($this->updateFields);
    }
}