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

use League\Csv\CharsetConverter;
use League\Csv\Writer;
use League\Csv\Reader;

/**
 * Class Csv
 *
 * @package App\Services\Csv
 */
class Csv
{
    /**
     * @var Reader
     */
    public Reader $reader;

    /**
     * @var Writer
     */
    public Writer $writer;

    /**
     * Return Reader.
     *
     * @return \League\Csv\Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * Return writer.
     *
     * @return \League\Csv\Writer
     */
    public function getWriter(): Writer
    {
        return $this->writer;
    }

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
     * Create reader from stream.
     *
     * @param $stream
     * @return void
     */
    public function readerCreateFromStream($stream)
    {
        $this->reader = Reader::createFromStream($stream);
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
     * Create writer from stream.
     *
     * @param $stream
     * @return void
     */
    public function writerCreateFromStream($stream)
    {
        $this->writer = Writer::createFromStream($stream);
    }

    /**
     * Set delimiter.
     *
     * @param string $delimiter
     * @throws \League\Csv\Exception
     */
    public function setDelimiter(string $delimiter = ',')
    {
        $this->reader->setDelimiter($delimiter);
    }

    /**
     * Set enclosure.
     *
     * @param string $enclosure
     * @throws \League\Csv\Exception
     */
    public function setEnclosure(string $enclosure = '"')
    {
        $this->reader->setEnclosure($enclosure);
    }

    /**
     * Set escape.
     *
     * @param string $escape
     * @throws \League\Csv\Exception
     */
    public function setEscape(string $escape = '\\')
    {
        $this->reader->setEscape($escape);
    }

    /**
     * Set header offset.
     *
     * @param int $offset
     * @return void
     * @throws \League\Csv\Exception
     */
    public function setHeaderOffset(int $offset = 0)
    {
        $this->reader->setHeaderOffset($offset);
    }

    /**
     * Return header row.
     *
     * @return string[]
     */
    public function getHeader(): array
    {
        return $this->reader->getHeader();
    }

    /**
     * Fetch all rows.
     *
     * @param array $header
     * @return \Iterator
     */
    public function getRecords(array $header = []): \Iterator
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
     * Return the count. If header is offset, then header not counted.
     *
     * @return int
     */
    public function getReaderCount(): int {
        return count($this->reader);
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