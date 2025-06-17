<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Process;

ini_set('auto_detect_line_endings', '1');
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '0');
ini_set('max_input_time', '0');
set_time_limit(0);
ignore_user_abort(true);

use App\Services\Csv\DarwinCoreCsvImport;
use Exception;

/**
 * Class DarwinCore
 */
class DarwinCore
{
    /**
     * @var MetaFile
     */
    public $metaFile;

    /**
     * DarwinCoreCsv Class
     *
     * @var DarwinCoreCsvImport
     */
    public $csv;

    /**
     * Id of project
     *
     * @var int
     */
    public $projectId;

    /**
     * Sets if media is core or extension in meta file
     *
     * @var bool
     */
    public $mediaIsCore;

    /**
     * Array for meta file fields: core and extension
     *
     * @var array
     */
    public $metaFields;

    /**
     * Construct
     */
    public function __construct(
        MetaFile $metaFile,
        DarwinCoreCsvImport $csv
    ) {
        $this->metaFile = $metaFile;
        $this->csv = $csv;
    }

    /**
     * Process Darwin Core Import.
     *
     * @throws \Exception
     */
    public function process($projectId, $directory)
    {
        $this->projectId = $projectId;
        $file = $directory.'/meta.xml';

        $this->checkFileExists($file);

        $meta = $this->metaFile->process($file);

        $this->mediaIsCore = $this->metaFile->getMediaIsCore();
        $this->metaFields = $this->metaFile->getMetaFields();

        // Set meta properties needed in handling csv file.
        $this->csv->setCsvMetaProperties($this->mediaIsCore, $this->metaFields, $this->projectId);

        // Load media first to create subjects
        $this->processCsvFile($directory);

        // Load occurrences
        $this->processCsvFile($directory, false);

        $this->metaFile->saveMetaFile($projectId, $meta);
    }

    /**
     * Check file exists.
     *
     * @throws \Exception
     */
    protected function checkFileExists($file)
    {
        if (! file_exists($file)) {
            throw new Exception(t('Required file missing from Darwin Core Archive. %s', $file));
        }
    }

    /**
     * Process a darwin core csv file
     *
     * @param  bool  $loadMedia
     *
     * @throws \Exception
     */
    protected function processCsvFile($directory, $loadMedia = true)
    {
        $type = $this->setFileType($loadMedia);
        $file = $this->setFilePath($directory, $type);

        $this->checkFileExists($file);

        $delimiter = $this->setDelimiter($type);
        $enclosure = $this->setEnclosure($type);
        $this->csv->loadCsvFile($file, $delimiter, $enclosure, $type, $loadMedia);
    }

    /**
     * Set file type being worked on from meta file
     *
     * @return string
     */
    public function setFileType($loadMedia)
    {
        if ($loadMedia === true) {
            return $this->mediaIsCore ? 'core' : 'extension';
        } else {
            return $this->mediaIsCore ? 'extension' : 'core';
        }
    }

    /**
     * Set File to work with
     *
     * @return string
     */
    public function setFilePath($directory, $type)
    {
        if ($type === 'core') {
            $file = $directory.'/'.$this->metaFile->getCoreFile();
        } else {
            $file = $directory.'/'.$this->metaFile->getExtensionFile();
        }

        return $file;
    }

    /**
     * Set delimiter
     *
     * @return mixed
     */
    public function setDelimiter($type)
    {
        return ($type === 'core') ? $this->metaFile->getCoreDelimiter() : $this->metaFile->getExtDelimiter();
    }

    /**
     * Set enclosure
     *
     * @return mixed
     */
    public function setEnclosure($type)
    {
        return ($type === 'core') ? $this->metaFile->getCoreEnclosure() : $this->metaFile->getExtEnclosure();
    }

    /**
     * Get duplicate records
     *
     * @return array
     */
    public function getDuplicates()
    {
        return $this->csv->getDuplicates();
    }

    /**
     * Get rejected media
     *
     * @return array
     */
    public function getRejectedMedia()
    {
        return $this->csv->getRejectedMedia();
    }

    /**
     * @return int
     */
    public function getSubjectCount()
    {
        return $this->csv->subjectCount;
    }
}
