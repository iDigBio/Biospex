<?php

namespace App\Services\Csv;

use League\Csv\Writer;
use League\Csv\Reader;

abstract class CsvAbstract
{
    /**
     * @var
     */
    public $reader;

    /**
     * @var
     */
    public $writer;

    /**
     * Create reader using file path
     * @param $file
     * @param string $delimiter
     * @param string $enclosure
     */
    public function readerCreateFromPath($file, $delimiter = ',', $enclosure = "")
    {
        $this->reader = Reader::createFromPath($file);
        $this->reader->setDelimiter($delimiter);
        if ( ! empty($enclosure)) {
            $this->reader->setEnclosure($enclosure);
        }
    }

    /**
     * Create writer from file path
     * @param $file
     */
    public function writerCreateFromPath($file)
    {
        $this->writer = Writer::createFromPath($file, 'w');
    }

    /**
     * Fetch rows
     * @return mixed
     */
    public function fetch()
    {
        return $this->reader->setOffset(1)->fetch();
    }

    /**
     * Return header row
     * @return mixed
     */
    public function getHeaderRow()
    {
        return $this->reader->fetchOne();
    }
}