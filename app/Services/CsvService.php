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

use League\Csv\CharsetConverter;
use League\Csv\Statement;
use League\Csv\Writer;
use League\Csv\Reader;

/**
 * Class CsvService
 *
 * @package App\Services
 */
class CsvService
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
     * Create writer from temp file object.
     */
    public function writerCreateFromTempFileObj()
    {
        $this->writer = Writer::createFromFileObject(new \SplTempFileObject());
    }

    /**
     * Create Statement.
     *
     * @return \League\Csv\Statement
     * @throws \League\Csv\Exception
     */
    public function statementCreate(): Statement
    {
        return Statement::create();
    }

    /**
     * Set delimiter.
     *
     * @param string $delimiter
     * @throws \League\Csv\Exception
     */
    public function setDelimiter($delimiter = ',')
    {
        $this->reader->setDelimiter($delimiter);
    }

    /**
     * Set enclosure.
     *
     * @param string $enclosure
     * @throws \League\Csv\Exception
     */
    public function setEnclosure($enclosure = '"')
    {
        $this->reader->setEnclosure($enclosure);
    }

    /**
     * Set escape.
     *
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
     * @param $row
     * @throws \League\Csv\CannotInsertRecord
     */
    public function insertOne($row)
    {
        $this->writer->insertOne($row);
    }

    /**
     * Insert all rows.
     * @param $rows
     * @throws \TypeError
     */
    public function insertAll($rows)
    {
        $this->writer->insertAll($rows);
    }

    /**
     * Set encoding.
     *
     * @return \League\Csv\CharsetConverter
     */
    public function setEncoding(): CharsetConverter
    {
        return (new CharsetConverter())
            ->inputEncoding('utf-8')
            ->outputEncoding('utf-8');
    }
}