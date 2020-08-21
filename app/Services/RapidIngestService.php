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

use App\Repositories\Interfaces\Header;
use App\Repositories\Interfaces\RapidRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapidIngestService
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
     * @var \App\Repositories\Interfaces\Header
     */
    private $headerInterface;

    /**
     * RapidIngestService constructor.
     *
     * @param \App\Services\CsvService $csvService
     * @param \App\Repositories\Interfaces\RapidRecord $rapidInterface
     * @param \App\Repositories\Interfaces\Header $headerInterface
     */
    public function __construct(CsvService $csvService, RapidRecord $rapidInterface, Header $headerInterface)
    {

        $this->csvService = $csvService;
        $this->rapidInterface = $rapidInterface;
        $this->headerInterface = $headerInterface;
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
        $header = $this->csvService->getHeader();

        $this->headerInterface->create(['header' => $header]);

        $rows = $this->csvService->getRecords($header);
        foreach ($rows as $offset => $row) {
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
     * unique gbifID_gbif + idigbio_uuid_idbP
     * @param $row
     * @return mixed
     */
    public function validateRow($row)
    {
        $count = $this->rapidInterface->validateRecord($row['gbifID_gbif'], $row['idigbio_uuid_idbP']);

        if ($count) {
            $this->errors[] = $row;
        }

        return $count;
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


        return route('admin.download.report', ['fileName' => $fileName]);
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
}