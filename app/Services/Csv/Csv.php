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
     * Create writer from file path.
     * 
     * @param $filePath
     */
    public function writerCreateFromPath($filePath)
    {
        $this->writer = Writer::createFromPath(new \SplFileObject($filePath, 'a+'), 'w');
    }

    /**
     * Fetch rows.
     * 
     * @return mixed
     */
    public function fetch()
    {
        return $this->reader->setOffset(1)->fetch();
    }

    /**
     * Fetch all rows.
     * 
     * @return mixed
     */
    public function fetchAll()
    {
        return $this->reader->fetchAll();
    }

    /**
     * Return header row.
     * 
     * @return mixed
     */
    public function getHeaderRow()
    {
        return $this->reader->fetchOne();
    }

    /**
     * Insert one row.
     * 
     * @param $row
     */
    public function insertOne($row)
    {
        $this->writer->insertOne($row);
    }

    /**
     * Insert all rows.
     * 
     * @param $rows
     */
    public function insertAll($rows)
    {
        $this->writer->insertAll($rows);
    }
}