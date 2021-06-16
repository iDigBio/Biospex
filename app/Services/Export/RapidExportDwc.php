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

use App\Models\Product;
use App\Services\CsvService;
use App\Services\Model\ProductModelService;
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
    private $metaFilePath;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $citationFilePath;

    /**
     * @var \App\Services\Model\ProductModelService
     */
    private $productModelService;

    /**
     * RapidExportDwc constructor.
     *
     * @param \App\Services\CsvService $csvService
     * @param \App\Services\Model\ProductModelService $productModelService
     */
    public function __construct(CsvService $csvService, ProductModelService $productModelService)
    {
        $this->csvService = $csvService;
        $this->productModelService = $productModelService;
        $this->metaFilePath = config('config.meta_file');
        $this->citationfilePath = config('config.citation_file');
    }

    /**
     * Process export.
     *
     * @param \App\Models\Product $product
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function process(Product $product)
    {
        $product->key === '00000000-0000-0000-0000-000000000000' ?
            $this->processAll($product->key) :
            $this->processProvider($product->key);

        $product->touch();
    }

    /**
     * Create full dwc export with all providers.
     *
     * @param string $key
     */
    private function processAll(string $key)
    {
        $files = [
            'occurrence.csv' => $this->getProductFilePath('occurrence.csv'),
            'meta.xml' => $this->metaFilePath,
            'citation.txt' => $this->citationFilePath
        ];

        $dwcFilePath = $this->getProductFilePath($key . '.zip');
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
        $tmpFilePath = \Storage::path('occurrence.csv');
        $this->buildCsv($key, $tmpFilePath);

        $files = [
            'occurrence.csv' => $tmpFilePath,
            'meta.xml' => $this->metaFilePath,
            'citation.txt' => $this->citationFilePath
        ];

        $dwcFilePath = $this->getProductFilePath($key . '.zip');
        $this->zipFile($files, $dwcFilePath);

        File::delete($tmpFilePath);
    }

    /**
     * Build csv for given provider.
     *
     * @param string $key
     * @param string $tmpFilePath
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    private function buildCsv(string $key, string $tmpFilePath)
    {
        $this->csvService->readerCreateFromPath($this->getProductFilePath('occurrence.csv'));
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

        File::put($tmpFilePath, $this->csvService->writer->toString());
    }

    /**
     * Create product or return first.
     *
     * @param string $key
     * @param string|null $name
     * @return mixed
     */
    public function getProductRecord(string $key, string $name = null)
    {
        return $name === null ?
            $this->productModelService->findBy('key', $key) :
            $this->productModelService->firstOrCreate(['key' => $key], ['name' => $name]);
    }
}