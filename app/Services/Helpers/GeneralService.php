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

namespace App\Services\Helpers;

use App\Models\Expedition;
use Storage;

/**
 * Class GeneralService
 */
class GeneralService
{
    /**
     * Encode a full url.
     */
    public function urlEncode($url): string
    {
        $parts = parse_url($url);
        $path_parts = array_map('rawurldecode', explode('/', $parts['path']));

        return $parts['scheme'].'://'.$parts['host'].implode('/', array_map('rawurlencode', $path_parts));
    }

    /**
     * @return mixed|null
     */
    public function getState($input): mixed
    {
        $states = [
            'Alabama' => 'AL',
            'Alaska' => 'AK',
            'Arizona' => 'AZ',
            'Arkansas' => 'AR',
            'California' => 'CA',
            'Colorado' => 'CO',
            'Connecticut' => 'CT',
            'Delaware' => 'DE',
            'District Of Columbia' => 'DC',
            'Florida' => 'FL',
            'Georgia' => 'GA',
            'Hawaii' => 'HI',
            'Idaho' => 'ID',
            'Illinois' => 'IL',
            'Indiana' => 'IN',
            'Iowa' => 'IA',
            'Kansas' => 'KS',
            'Kentucky' => 'KY',
            'Louisiana' => 'LA',
            'Maine' => 'ME',
            'Maryland' => 'MD',
            'Massachusetts' => 'MA',
            'Michigan' => 'MI',
            'Minnesota' => 'MN',
            'Mississippi' => 'MS',
            'Missouri' => 'MO',
            'Montana' => 'MT',
            'Nebraska' => 'NE',
            'Nevada' => 'NV',
            'New Hampshire' => 'NH',
            'New Jersey' => 'NJ',
            'New Mexico' => 'NM',
            'New York' => 'NY',
            'North Carolina' => 'NC',
            'North Dakota' => 'ND',
            'Ohio' => 'OH',
            'Oklahoma' => 'OK',
            'Oregon' => 'OR',
            'Pennsylvania' => 'PA',
            'Rhode Island' => 'RI',
            'South Carolina' => 'SC',
            'South Dakota' => 'SD',
            'Tennessee' => 'TN',
            'Texas' => 'TX',
            'Utah' => 'UT',
            'Vermont' => 'VT',
            'Virginia' => 'VA',
            'Washington' => 'WA',
            'West Virginia' => 'WV',
            'Wisconsin' => 'WI',
            'Wyoming' => 'WY',
        ];

        foreach ($states as $name => $abbr) {
            if (strtolower($input) === strtolower($name)) {
                return $abbr;
            }
        }

        return null;
    }

    /**
     * Convert a string to UTF-8 encoding using native PHP functions.
     *
     * @param  string|null  $str  String to encode
     * @param  string|null  $inputEnc  Source encoding (auto-detected if null)
     * @return string UTF-8 encoded string
     */
    public function forceUtf8(?string $str, ?string $inputEnc = null): string
    {
        // Handle null/empty input
        if ($str === null || $str === '') {
            return '';
        }

        // Check if already valid UTF-8
        if ($this->isUtf8($str)) {
            return $str;
        }

        // Auto-detect encoding if not provided
        if ($inputEnc === null) {
            $inputEnc = mb_detect_encoding($str, ['UTF-8', 'ISO-8859-1', 'WINDOWS-1252', 'ASCII'], true);
            if ($inputEnc === false) {
                $inputEnc = 'WINDOWS-1252'; // fallback
            }
        }

        // Convert to UTF-8
        $converted = mb_convert_encoding($str, 'UTF-8', $inputEnc);

        // Return converted string or original if conversion failed
        return $converted !== false ? $converted : $str;
    }

    /**
     * Check if a string is valid UTF-8 using native PHP function.
     *
     * @param  string|null  $str  String to check
     */
    private function isUtf8(?string $str): bool
    {
        return $str !== null && mb_check_encoding($str, 'UTF-8');
    }

    /**
     * Fix malformed UTF-8 encoding issues.
     * This replaces ForceUTF8\Encoding::fixUTF8() functionality.
     *
     * @param  string|null  $str  String to fix
     * @return string Fixed UTF-8 string
     */
    public function fixUtf8(?string $str): string
    {
        if ($str === null || $str === '') {
            return '';
        }

        // Fix malformed UTF-8 by converting from UTF-8 to UTF-8
        // This removes invalid sequences
        $fixed = mb_convert_encoding($str, 'UTF-8', 'UTF-8');

        return $fixed !== false ? $fixed : '';
    }

    /**
     * Give file size in human-readable form.
     *
     * @param  int  $decimals
     */
    public function humanFileSize($bytes, $decimals = 2): string
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$size[$factor];
    }

    /**
     * Return banner file name if exists.
     */
    public function projectBannerFileName(?string $name = null): ?string
    {
        return $name ?? 'banner-trees.jpg';
    }

    /**
     * Return banner file url if exists.
     */
    public function projectBannerFileUrl(?string $name = null): ?string
    {
        return $name === null ?
            '/images/habitat-banners/banner-trees.jpg' :
            '/images/habitat-banners/'.$name;
    }

    /**
     * Return default logo for projects.
     */
    public function projectDefaultLogo(): string
    {
        return '/images/placeholders/project.png';
    }

    /**
     * Return default logo for expeditions.
     */
    public function expeditionDefaultLogo(): string
    {
        return '/images/placeholders/card-image-place-holder02.jpg';
    }

    /**
     * Check if download file exists.
     * TODO: Refactor this after changing and moving download file storage.
     */
    public function downloadFileExists(string $file, string $type, ?int $actorId = null): bool
    {
        if ($actorId == config('zooniverse.actor_id')) {
            return $this->checkZooniverseFile($type, $file);
        }

        if ($actorId == config('geolocate.actor_id')) {
            return $this->checkGeoLocateFile($type, $file);
        }

        return false;
    }

    /**
     * Check if download file exists.
     */
    private function checkZooniverseFile(string $type, string $file): bool
    {
        if ($type === 'export') {
            return Storage::disk('s3')->exists(config('config.export_dir').'/'.$file);
        } elseif ($type === 'report') {
            return Storage::disk('s3')->exists(config('config.report_dir').'/'.$file);
        } else {
            return Storage::disk('s3')->exists(config('zooniverse.directory.parent').'/'.$type.'/'.$file);
        }
    }

    /**
     * Check if download file exists.
     */
    private function checkGeoLocateFile(string $type, string $file): bool
    {
        return Storage::disk('s3')->exists(config('geolocate.dir.parent').'/'.$type.'/'.$file);
    }

    /**
     * Get file size of download file.
     */
    public function downloadFileSize(string $file, string $type, ?int $actorId = null): int
    {

        if ($actorId == config('zooniverse.actor_id')) {
            return $this->checkZooniverseFileSize($file, $type);
        }

        if ($actorId == config('geolocate.actor_id')) {
            return $this->checkGeoLocateFileSize($file, $type);
        }

        return 0;
    }

    /**
     * Get file size of download file.
     */
    private function checkZooniverseFileSize(string $file, string $type): int
    {
        if ($type === 'export') {
            return Storage::disk('s3')->size(config('config.export_dir').'/'.$file);
        } elseif ($type === 'report') {
            return Storage::disk('s3')->size(config('config.report_dir').'/'.$file);
        } else {
            return Storage::disk('s3')->size(config('zooniverse.directory.parent').'/'.$type.'/'.$file);
        }
    }

    /**
     * Get file size of download file.
     */
    private function checkGeoLocateFileSize(string $file, string $type): int
    {
        return Storage::disk('s3')->size(config('geolocate.dir.parent').'/'.$type.'/'.$file);
    }

    /**
     * Check subjects and export file existence.
     */
    public function zooniverseExportFileCheck(Expedition $expedition): bool
    {
        return isset($expedition->zooniverseExport->file) && Storage::disk('s3')->exists(config('config.export_dir').'/'.$expedition->zooniverseExport->file);
    }

    /**
     * Check panoptes workflow and project set.
     */
    public function checkPanoptesWorkflow(Expedition $expedition): bool
    {
        return isset($expedition->panoptesProject) &&
            $expedition->panoptesProject->panoptes_workflow_id !== null &&
            $expedition->panoptesProject->panoptes_project_id !== null;
    }
}
