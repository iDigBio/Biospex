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

use Illuminate\Support\Facades\File;
use Storage;

/**
 * Class RapidFileService
 *
 * @package App\Services
 */
class RapidFileService
{
    /**
     * @var string
     */
    private $headerFilePath;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $validationFields;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $protectedFields;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $exportExtensions;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $reservedColumns;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $defaultGridView;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $columnTags;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $importsPath;

    public function __construct()
    {
        $this->headerFilePath = config('config.header_import_file');
        $this->validationFields = config('config.validation_fields');
        $this->protectedFields = config('config.protected_fields');
        $this->exportExtensions = config('config.export_extensions');
        $this->reservedColumns = config('config.reserved_columns');
        $this->defaultGridView = config('config.default_grid_visible');
        $this->columnTags = config('config.column_tags');
        $this->importsPath = config('config.rapid_import_dir');
    }

    /**
     * Get validation fields for rapid records.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getValidationFields()
    {
        return $this->validationFields;
    }

    /**
     * Store header to file.
     *
     * @param array $header
     */
    public function storeHeader(array $header = [])
    {
        Storage::put($this->headerFilePath, json_encode($header));
    }

    /**
     * Update header.
     *
     * @param array $csvHeader
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateHeader(array $csvHeader = [])
    {
        $rapidHeader = $this->getHeader();

        $diff = collect($csvHeader)->diff($rapidHeader)->reject(function ($field) {
            return in_array($field, $this->protectedFields);
        });

        $rapidHeader = collect($rapidHeader)->concat($diff)->toArray();

        $this->storeHeader($rapidHeader);
    }

    /**
     * Return headers from file.
     *
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getHeader(): array
    {
        return json_decode(Storage::get($this->headerFilePath), true);
    }

    /**
     * Return protected fields.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getProtectedFields()
    {
        return $this->protectedFields;
    }

    /**
     * Return export extensions.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getExportExtensions()
    {
        return $this->exportExtensions;
    }

    /**
     * Return reserved columns.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getReservedColumns()
    {
        return $this->reservedColumns;
    }

    /**
     * Get destination fields file.
     *
     * @param string $destination
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getDestinationFieldFile(string $destination)
    {
        return json_decode(File::get(config('config.'.$destination.'_fields_file')), true);
    }

    /**
     * Get default grid view.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getDefaultGridView()
    {
        return $this->defaultGridView;
    }

    /**
     * Return column tags.
     * 
     * @return array|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application
     */
    public function getColumnTags(): array
    {
        return $this->columnTags;
    }

    /**
     * Return import path.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getImportsPath()
    {
        return $this->importsPath;
    }
}