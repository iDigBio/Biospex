<?php

namespace Biospex\Services\Csv;

use League\Csv\Writer;
use League\Csv\Reader;

abstract class CsvAbstract
{
    public $reader;
    public $writer;

    public function readerCreateFromPath($file, $delimiter = ',', $enclosure = "")
    {
        $this->reader = Reader::createFromPath($file);
        $this->reader->setDelimiter($delimiter);
        if ( ! empty($enclosure)) {
            $this->reader->setEnclosure($enclosure);
        }
    }

    public function writerCreateFromPath($file)
    {
        $this->writer = Writer::createFromPath($file, 'w');
    }

    public function readerIterateEach($callback, $type = null)
    {
        $this->reader->each(function ($row, $index, $iterator) use ($callback, $type) {
            if (empty($row[0])) {
                return false;
            }

            if (method_exists($callback[0], $callback[1])) {
                return call_user_func_array($callback, [$row, $index, $iterator, $type]);
            }

            return true;
        });
    }

    public function iterateOverRows()
    {
        $iterator = $this->reader->setOffset(1)->query(function ($row) {
            return $row;
        });

        return $iterator;
    }

    public function getHeaderRow()
    {
        return $this->reader->fetchOne();
    }
}