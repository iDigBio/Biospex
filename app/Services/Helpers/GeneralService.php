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
     * Try to convert a string to UTF-8.
     *
     * @author Thomas Scholz <http://toscho.de>
     *
     * @param  string  $str  String to encode
     * @param  string  $inputEnc  Maybe the source encoding.
     *                            Set to NULL if you are not sure. iconv() will fail then.
     */
    public function forceUtf8($str, $inputEnc = 'WINDOWS-1252'): string
    {
        if ($this->isUtf8($str)) { // nothing to do
            return $str;
        }

        if (strtoupper($inputEnc) === 'ISO-8859-1') {
            return utf8_encode($str);
        }

        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($str, 'UTF-8', $inputEnc);
        }

        if (function_exists('iconv')) {
            return iconv($inputEnc, 'UTF-8', $str);
        }

        // You could also just return the original string.
        return 'Could not convert string to UTF-8';
    }

    /**
     * Check for UTF-8 compatibility
     *
     * Regex from Martin Dürst
     *
     * @source http://www.w3.org/International/questions/qa-forms-utf-8.en.php
     *
     * @param  string  $str  String to check
     */
    private function isUtf8($str): bool
    {
        return preg_match("/^(
         [\x09\x0A\x0D\x20-\x7E]            # ASCII
       | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
       |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
       | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
       |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
       |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
       | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
       |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
      )*$/x", $str);
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
