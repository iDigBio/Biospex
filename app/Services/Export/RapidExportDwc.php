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
use App\Services\RapidServiceBase;
use Illuminate\Support\Facades\File;

/**
 * Class RapidExportDwc
 *
 * @package App\Services\Export
 */
class RapidExportDwc extends RapidServiceBase
{

    /**
     * @var \App\Services\CsvService
     */
    private $csvService;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $occurrenceFilePath;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $metaFilePath;

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
        $this->occurrenceFilePath = config('config.occurrence_file');
        $this->metaFilePath = config('config.meta_file');
    }

    /**
     * @param string $key
     */
    public function process(string $key)
    {
        $key === '' ? $this->processAll($key) : $this->processProvider($key);
    }

    /**
     * Create full dwc export with all providers.
     *
     * @param string $key
     */
    private function processAll(string $key)
    {
        $files = [
            'occurrence.csv' => $this->occurrenceFilePath,
            'meta.xml' => $this->metaFilePath
        ];

        $dwcFilePath = $this->getExportFilePath($key . '.zip');
        $this->zipFile($files, $dwcFilePath);
    }

    /**
     * Create dwc for given provider.
     *
     * @param string $key
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    private function processProvider(string $key)
    {
        $csvFilePath = \Storage::path(config('config.rapid_export_dir') . '/occurrence.csv');
        $this->buildCsv($key, $csvFilePath);

        $files = [
            'occurrence.csv' => $csvFilePath,
            'meta.xml' => $this->metaFilePath
        ];

        $dwcFilePath = $this->getExportFilePath($key . '.zip');
        $this->zipFile($files, $dwcFilePath);

        File::delete($csvFilePath);
    }

    /**
     * Build csv for given provider.
     *
     * @param string $key
     * @param string $csvFilePath
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    private function buildCsv(string $key, string $csvFilePath)
    {
        $this->csvService->readerCreateFromPath($this->occurrenceFilePath);
        $this->csvService->setDelimiter();
        $this->csvService->setEnclosure();
        $this->csvService->setHeaderOffset();

        $stmt = $this->csvService->statementCreate()->where(function(array $record) use($key) {
            return $record['datasetKey'] === $key;
        });

        $records = $stmt->process($this->csvService->reader);

        $this->csvService->writerCreateFromTempFileObj();
        $this->csvService->writer->addFormatter($this->csvService->setEncoding());

        $first = true;
        foreach ($records as $record) {
            if ($first) {
                $this->csvService->insertOne(array_keys($record));
                $first = false;
            }
            $this->csvService->insertOne($record);
        }

        File::put($csvFilePath, $this->csvService->writer->toString());
    }
}