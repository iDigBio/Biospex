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

namespace App\Services\Csv;

use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use League\Csv\Reader;

class Csv
{
    /**
     * @var Reader
     */
    public $reader;

    /**
     * @var Writer
     */
    public $writer;

    /**
     * Create reader using file path.
     *
     * @param $file
     */
    public function readerCreateFromPath($file)
    {
        $this->reader = Reader::createFromPath($file);
    }

    /**
     * Create writer from file path.
     *
     * @param $filePath
     */
    public function writerCreateFromPath($filePath)
    {
        $this->writer = Writer::createFromPath($filePath, 'w+');
    }

    /**
     * @param string $delimiter
     * @throws \League\Csv\Exception
     */
    public function setDelimiter($delimiter = ',')
    {
        $this->reader->setDelimiter($delimiter);
    }

    /**
     * @param string $enclosure
     * @throws \League\Csv\Exception
     */
    public function setEnclosure($enclosure = '"')
    {
        $this->reader->setEnclosure($enclosure);
    }

    /**
     * @param string $escape
     * @throws \League\Csv\Exception
     */
    public function setEscape($escape = '\\')
    {
        $this->reader->setEscape($escape);
    }

    /**
     * Set header offset.
     *
     * @param int $offset
     * @throws \League\Csv\Exception
     */
    public function setHeaderOffset($offset = 0)
    {
        $this->reader->setHeaderOffset($offset);
    }

    /**
     * Return header row.
     *
     * @return mixed
     */
    public function getHeader()
    {
        return $this->reader->getHeader();
    }

    /**
     * Fetch all rows.
     *
     * @param array $header
     * @return mixed
     */
    public function getRecords($header = [])
    {
        return $this->reader->getRecords($header);
    }

    /**
     * Insert one row.
     *
     * @param $row
     * @throws \League\Csv\CannotInsertRecord
     */
    public function insertOne($row)
    {
        $this->writer->insertOne($row);
    }

    /**
     * Insert all rows.
     *
     * @param $rows
     * @throws \TypeError
     */
    public function insertAll($rows)
    {
        $this->writer->insertAll($rows);
    }

    /**
     * Create Report Csv.
     *
     * @param array $data
     * @param string $fileName
     * @return string|null
     * @throws \League\Csv\CannotInsertRecord
     */
    public function createReportCsv(array $data, string $fileName)
    {
        if (! isset($data) || empty($data)) {
            return null;
        }

        $header = array_keys($data[0]);

        $file = Storage::path(config('config.reports_dir').'/'.$fileName);
        $this->writerCreateFromPath($file);
        $this->insertOne($header);
        $this->insertAll($data);

        return base64_encode($fileName);
    }
}