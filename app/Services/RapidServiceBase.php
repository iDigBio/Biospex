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
    public function getReservedColumns()
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
     * Build the header txt file for version file export.
     *
     * @param array $data
     */
    public function buildExportHeader(array $data = [])
    {
        collect($data)->each(function($value) {
            Storage::append(config('config.rapid_version_dir') . '/header.txt', $value);
        });
    }

    /**
     * Return header export file path.
     *
     * @return string
     */
    public function getExportHeaderFile(): string
    {
        return Storage::path(config('config.rapid_version_dir') . '/header.txt');
    }

    /**
     * Delete header file used for version export.
     */
    public function deleteExportHeaderFile()
    {
        Storage::delete(config('config.rapid_version_dir') . '/header.txt');
    }

    /**
     * Get version file path.
     *
     * @param string $versionFileName
     * @return string
     */
    public function getVersionFilePath(string $versionFileName): string
    {
        return Storage::path(config('config.rapid_version_dir') . '/' . $versionFileName);
    }

    /**
     * Delete version csv file
     *
     * @param string $versionFileName
     */
    public function deleteVersionFile(string $versionFileName)
    {
        Storage::delete(config('config.rapid_version_dir') . '/' . $versionFileName);
    }

    /**
     * Get version file size for check.
     *
     * @param string $versionFileName
     * @return int
     */
    public function getVersionFileSize(string $versionFileName): int
    {
        return Storage::size(config('config.rapid_version_dir') . '/' . $versionFileName);
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
        $mapped = collect($header)->mapToGroups(function($value) use($tags){
            foreach ($tags as $tag) {
                if (preg_match('/'.$tag.'/', $value, $matches)) {
                    return [$matches[0] => $value];
                }
            }
            return ['unused' => $value];
        });

        return $mapped->forget('unused');
    }

    /**
     * Create zip file for version export.
     *
     * @param string $versionFileName
     * @param string $zipFileName
     */
    public function zipVersionFile(string $versionFileName, string $zipFileName)
    {
        $zip = new ZipArchive;
        if ($zip->open(Storage::path(config('config.rapid_version_dir')) . '/' . $zipFileName, ZipArchive::CREATE) === TRUE)
        {
            // Add files to the zip file
            $zip->addFile(Storage::path(config('config.rapid_version_dir')) . '/' . $versionFileName, $versionFileName);

            // All files are added, so close the zip file.
            $zip->close();
        }
    }
}