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

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Storage;
use ZipArchive;

/**
 * Class RapidServiceBase
 *
 * @package App\Services
 */
class RapidServiceBase
{
    /**
     * Get validation fields for rapid records.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected function getValidationFields()
    {
        return config('config.validation_fields');
    }

    /**
     * Return protected fields.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected function getProtectedFields()
    {
        return config('config.protected_fields');
    }

    /**
     * Return export extensions.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getExportExtensions()
    {
        return config('config.export_extensions');
    }

    /**
     * Return reserved columns.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getConfigReservedColumns()
    {
        return config('config.reserved_columns');
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
        return config('config.default_grid_visible');
    }

    /**
     * Return column tags.
     *
     * @return array|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application
     */
    public function getColumnTags(): array
    {
        return config('config.column_tags');
    }

    /**
     * Return import path.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getImportsPath()
    {
        return config('config.rapid_import_dir');
    }

    /**
     * Return import path.
     *
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getImportsTmpPath()
    {
        return config('config.rapid_import_dir').'/tmp';
    }

    /**
     * Get export file path.
     *
     * @param string $fileName
     * @return string
     */
    public function getExportFilePath(string $fileName): string
    {
        return Storage::path(config('config.rapid_export_dir').'/'.$fileName);
    }

    /**
     * Get version file path.
     *
     * @param string $fileName
     * @return string
     */
    public function getVersionFilePath(string $fileName): string
    {
        return Storage::path(config('config.rapid_version_dir').'/'.$fileName);
    }

    /**
     * Delete version csv file
     *
     * @param string $fileName
     */
    public function deleteVersionFile(string $fileName)
    {
        Storage::delete(config('config.rapid_version_dir').'/'.$fileName);
    }

    /**
     * Map header columns to tags.
     *
     * @param array $header
     * @param array $tags
     * @return \Illuminate\Support\Collection
     */
    public function mapColumns(array $header, array $tags): Collection
    {
        return collect($header)->mapToGroups(function ($value) use ($tags) {
            foreach ($tags as $tag) {
                if (preg_match('/'.$tag.'/', $value, $matches)) {
                    return [$matches[0] => $value];
                }
            }

            return ['unused' => $value];
        })->forget('unused')->map(function($value, $key){
            return $value->sort()->values();
        });
    }

    /**
     * Create zip file for version export.
     *
     * @param string $fileName
     * @param string $filePath
     * @param string $zipFilePath
     */
    public function zipVersionFile(string $fileName, string $filePath, string $zipFilePath)
    {
        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === true) {
            // Add files to the zip file
            $zip->addFile($filePath, $fileName);

            // All files are added, so close the zip file.
            $zip->close();
        }
    }
}