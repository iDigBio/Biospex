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

namespace App\Services\Download;

use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\ArrayShape;
use League\Csv\Reader;

/**
 * Class DownloadFileBase
 *
 * @package App\Services\Download
 */
class DownloadFileBase
{
    /**
     * @var string|null
     */
    public string|null $filePath = null;

    /**
     * @var string|null
     */
    public string|null $storagePath = null;

    /**
     * @var string|null
     */
    public string|null $headerFileName = null;

    /**
     * Set file path.
     *
     * @param string $fileDir
     * @param string $fileName
     */
    public function setFilePath(string $fileDir, string $fileName)
    {
        $this->filePath = $fileDir . '/' . $fileName;
    }

    /**
     * Set header file name.
     *
     * @param string $fileName
     */
    public function setHeaderFileName(string $fileName)
    {
        $this->headerFileName = $fileName;
    }


    /**
     * Check file exists to download.
     * TODO remove and change to S3 method below
     * @return bool
     * @throws \Exception
     */
    public function checkFileExists(): bool
    {
        if(!isset($this->filePath)) {
            throw new \Exception(t('File path must be set.'));
        }

        return Storage::exists($this->filePath);
    }

    /**
     * Check file exists to download.
     *
     * @return bool
     * @throws \Exception
     */
    public function checkS3FileExists(): bool
    {
        if(!isset($this->filePath)) {
            throw new \Exception(t('File path must be set.'));
        }

        return Storage::disk('s3')->exists($this->filePath);
    }

    /**
     *  Get storage path.
     *
     * @throws \Exception
     */
    public function setStoragePath()
    {
        if(!isset($this->filePath)) {
            throw new \Exception(t('File path must be set.'));
        }

        $this->storagePath = Storage::path($this->filePath);
    }

    /**
     * Get storage path.
     *
     * @return string|null
     * @throws \Exception
     */
    public function getStoragePath()
    {
        if(!isset($this->storagePath)) {
            throw new \Exception(t('Storage file path must be set.'));
        }

        return $this->storagePath;
    }
    /**
     * Set headers for csv files.
     *
     * @return string[]
     * @throws \Exception
     */
    public function setCsvHeaders(): array
    {
        if(!isset($this->headerFileName)) {
            throw new \Exception(t('File name must be set.'));
        }

        return [
            'Content-Encoding' => 'none',
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $this->headerFileName . '"',
            'Content-Description' => 'File Transfer',
        ];
    }

    /**
     * Set headers for html files.
     *
     * @return string[]
     * @throws \Exception
     */
    public function setHtmlHeaders(): array
    {
        if(!isset($this->headerFileName)) {
            throw new \Exception(t('File name must be set.'));
        }

        return [
            'Content-Encoding' => 'none',
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $this->headerFileName . '"',
            'Content-Description' => 'File Transfer',
        ];
    }

    /**
     * Set headers for tar files.
     *
     * @return string[]
     * @throws \Exception
     */
    #[ArrayShape(['Content-Type' => "string", 'Content-disposition' => "string"])]
    public function setTarHeaders(): array
    {
        if(!isset($this->headerFileName)) {
            throw new \Exception(t('File name must be set.'));
        }

        return [
            'Content-Type'        => 'application/x-compressed',
            'Content-disposition' => 'attachment; filename="' . $this->headerFileName . '"',
        ];
    }

    /**
     * Set Zip download headers.
     *
     * @return string[]
     * @throws \Exception
     */
    #[ArrayShape(['Content-Type'              => "string",
                  'Content-Transfer-Encoding' => "string",
                  'Content-disposition'       => "string"
    ])]
    public function setZipHeaders(): array
    {
        if(!isset($this->headerFileName)) {
            throw new \Exception(t('File name must be set.'));
        }

        return [
            'Content-Type'        => 'application/zip',
            'Content-Transfer-Encoding' => 'Binary',
            'Content-disposition' => 'attachment; filename="' . $this->headerFileName . '"',
        ];
    }

    /**
     * Set reader for csv and return.
     *
     * @return \League\Csv\AbstractCsv|\League\Csv\Reader
     * @throws \Exception
     */
    public function getReader()
    {
        if(!isset($this->storagePath)) {
            throw new \Exception(t('Storage path must be set.'));
        }

        $reader = Reader::createFromPath($this->storagePath, 'r');
        $reader->setOutputBOM(Reader::BOM_UTF8);

        return $reader;
    }

}