<?php

namespace App\Services\Csv;

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
     *
     * @throws \Exception
     */
    public function setDelimiter($delimiter = ',')
    {
        $this->reader->setDelimiter($delimiter);
    }

    /**
     * @param string $enclosure
     *
     * @throws \Exception
     */
    public function setEnclosure($enclosure = '"')
    {
        $this->reader->setEnclosure($enclosure);
    }

    /**
     * @param string $escape
     *
     * @throws \Exception
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
}