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

use Carbon\Carbon;
use Exception;
use Schema;
use Storage;

/**
 * Class GeneralHelper
 *
 * @package App\Services\Helpers
 */
class GeneralHelper
{
    /**
     * Encode a full url.
     *
     * @param $url
     * @return string
     */
    public function urlEncode($url)
    {
        $parts = parse_url($url);
        $path_parts = array_map('rawurldecode', explode('/', $parts['path']));

        return $parts['scheme'].'://'.$parts['host'].implode('/', array_map('rawurlencode', $path_parts));
    }

    /**
     * Round up to an integer, then to the nearest multiple of 5.
     * Used for scaling project page percent complete.
     *
     * @param $n
     * @param int $x
     * @return float
     */
    public function roundUpToAnyFive($n, $x = 5)
    {
        return (ceil($n) % $x === 0) ? ceil($n) : round(($n + $x / 2) / $x) * $x;
    }

    /**
     * Check if table has index.
     *
     * @param $table
     * @param $index
     * @return bool
     */
    public function tableHasIndex($table, $index)
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $doctrineTable = $dbSchemaManager->listTableDetails($table);

        return $doctrineTable->hasIndex($index);
    }

    /**
     * @param $string
     * @return string
     */
    public function decamelize($string)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

    /**
     * @param $input
     * @return mixed|null
     */
    public function getState($input)
    {
        $states = [
            'Alabama'              => 'AL',
            'Alaska'               => 'AK',
            'Arizona'              => 'AZ',
            'Arkansas'             => 'AR',
            'California'           => 'CA',
            'Colorado'             => 'CO',
            'Connecticut'          => 'CT',
            'Delaware'             => 'DE',
            'District Of Columbia' => 'DC',
            'Florida'              => 'FL',
            'Georgia'              => 'GA',
            'Hawaii'               => 'HI',
            'Idaho'                => 'ID',
            'Illinois'             => 'IL',
            'Indiana'              => 'IN',
            'Iowa'                 => 'IA',
            'Kansas'               => 'KS',
            'Kentucky'             => 'KY',
            'Louisiana'            => 'LA',
            'Maine'                => 'ME',
            'Maryland'             => 'MD',
            'Massachusetts'        => 'MA',
            'Michigan'             => 'MI',
            'Minnesota'            => 'MN',
            'Mississippi'          => 'MS',
            'Missouri'             => 'MO',
            'Montana'              => 'MT',
            'Nebraska'             => 'NE',
            'Nevada'               => 'NV',
            'New Hampshire'        => 'NH',
            'New Jersey'           => 'NJ',
            'New Mexico'           => 'NM',
            'New York'             => 'NY',
            'North Carolina'       => 'NC',
            'North Dakota'         => 'ND',
            'Ohio'                 => 'OH',
            'Oklahoma'             => 'OK',
            'Oregon'               => 'OR',
            'Pennsylvania'         => 'PA',
            'Rhode Island'         => 'RI',
            'South Carolina'       => 'SC',
            'South Dakota'         => 'SD',
            'Tennessee'            => 'TN',
            'Texas'                => 'TX',
            'Utah'                 => 'UT',
            'Vermont'              => 'VT',
            'Virginia'             => 'VA',
            'Washington'           => 'WA',
            'West Virginia'        => 'WV',
            'Wisconsin'            => 'WI',
            'Wyoming'              => 'WY',
        ];

        foreach ($states as $name => $abbr) {
            if (strtolower($input) === strtolower($name)) {
                return $abbr;
            }
        }

        return null;
    }

    /**
     * Turn camel case to words with spacing.
     *
     * @param $string
     * @return string
     */
    public function camelCaseToWords($string)
    {
        $split_data = preg_split('/(?=[A-Z])/', $string);

        return ucwords(implode(' ', $split_data));
    }

    /**
     * Check for UTF-8 compatibility
     *
     * Regex from Martin Dürst
     * @source http://www.w3.org/International/questions/qa-forms-utf-8.en.php
     *
     * @param string $str String to check
     * @return boolean
     */
    public function isUtf8($str)
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
     * Try to convert a string to UTF-8.
     *
     * @author Thomas Scholz <http://toscho.de>
     * @param string $str String to encode
     * @param string $inputEnc Maybe the source encoding.
     *               Set to NULL if you are not sure. iconv() will fail then.
     * @return string
     */
    public function forceUtf8($str, $inputEnc = 'WINDOWS-1252')
    {
        if ($this->isUtf8($str)) // nothing to do
        {
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
     * Give file size in human readable form.
     *
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    public function humanFileSize($bytes, $decimals = 2)
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$size[$factor];
    }

    /**
     * Check event is before start date.
     *
     * @param $event
     * @return bool
     */
    public function eventBefore($event)
    {
        $start_date = $event->start_date->setTimezone($event->timezone);
        $now = Carbon::now($event->timezone);

        return $now->isBefore($start_date);
    }

    /**
     * Check event in progress.
     *
     * @param $event
     * @return bool
     */
    public function eventActive($event)
    {
        $start_date = $event->start_date->setTimezone($event->timezone);
        $end_date = $event->end_date->setTimezone($event->timezone);
        $now = Carbon::now($event->timezone);

        return $now->between($start_date, $end_date);
    }

    /**
     * Check if event is over.
     *
     * @param $event
     * @return bool
     */
    public function eventAfter($event)
    {
        $end_date = $event->end_date->setTimezone($event->timezone);
        $now = Carbon::now($event->timezone);

        return $now->gt($end_date);
    }

    /**
     * Convert uuid value to bin for lookup.
     *
     * @param $value
     * @return false|string|void
     */
    public function uuidToBin($value)
    {
        if ($value === null) {
            return;
        }

        return pack('H*', str_replace('-', '', $value));
    }

    /**
     * Calculate AmChart height based on expedition count.
     *
     * @param $count
     * @return int|null
     */
    public function amChartHeight($count)
    {
        if ($count === 0) {
            return null;
        }

        $default = 264;
        for ($i = 1; $i <= $count; $i++) {
            $default = $default + 20;
        }

        return $default;
    }

    /**
     * Calculate AmChart height based on expedition count.
     *
     * @param $count
     * @return int|null
     */
    public function amLegendHeight($count)
    {
        if ($count === 0) {
            return null;
        }

        return $count * 25;
    }

    /**
     * Return banner file name if exists.
     *
     * @param null $name
     * @return mixed
     */
    public function projectBannerFileName($name = null)
    {
        return $name ?? 'banner-trees.jpg';
    }

    /**
     * @param null $name
     * @return mixed
     */
    public function projectBannerFileUrl($name = null)
    {
        return $name === null ?
            '/images/habitat-banners/banner-trees.jpg' :
            '/images/habitat-banners/'.$name;
    }

    /**
     * Return default logo for projects.
     *
     * @return mixed
     */
    public function projectDefaultLogo()
    {
        return '/images/placeholders/project.png';
    }

    /**
     * Return default logo for expeditions.
     *
     * @return mixed
     */
    public function expeditionDefaultLogo()
    {
        return '/images/placeholders/card-image-place-holder02.jpg';
    }

    /**
     * Check if download file exists.
     *
     * @param $type
     * @param $file
     * @return bool
     */
    public function downloadFileExists($type, $file)
    {
        if ($type === 'export') {
            return Storage::exists(config('config.export_dir').'/'.$file);
        }

        if ($type === 'report') {
            return Storage::exists(config('config.reports_dir').'/'.$file);
        }

        return Storage::exists(config('config.nfn_downloads_dir').'/'.$type.'/'.$file);
    }

    /**
     * Get file size of download file.
     *
     * @param $type
     * @param $file
     * @return int
     */
    public function downloadFileSize($type, $file)
    {
        if ($type === 'export') {
            return Storage::size(config('config.export_dir').'/'.$file);
        }

        if ($type === 'report') {
            return Storage::size(config('config.reports_dir').'/'.$file);
        }

        return Storage::size(config('config.nfn_downloads_dir').'/'.$type.'/'.$file);
    }

    /**
     * Base encode string.
     *
     * @param string $bin
     * @return string
     */
    public function base64UrlEncode(string $bin): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($bin));
    }

    /**
     * Base decode string.
     *
     * @param string $str
     * @return string
     */
    public function base64UrlDecode(string $str): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $str));
    }
}