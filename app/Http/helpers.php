<?php
use Biospex\Models\Transcription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

/**
 * Encode a full url.
 *
 * @param $url
 * @return string
 */
function url_encode($url)
{
    $parts = parse_url($url);
    $path_parts = array_map('rawurldecode', explode('/', $parts['path']));

    return
        $parts['scheme'] . '://' .
        $parts['host'] .
        implode('/', array_map('rawurlencode', $path_parts));
}

/**
 * Push messages to session.
 *
 * @param $key
 * @param $value
 */
function session_flash_push($key, $value)
{
    $values = Session::get($key, []);
    $values[] = $value;
    Session::flash($key, $values);
}

/**
 * Round up to an integer, then to the nearest multiple of 5
 * Used for scaling project page percent complete
 *
 * @param $n
 * @param int $x
 * @return float
 */
function round_up_to_any_five($n, $x = 5)
{
    return (ceil($n) % $x === 0) ? ceil($n) : round(($n + $x / 2) / $x) * $x;
}

/**
 * Format date using timezone and format.
 *
 * @param $date
 * @param null $format
 * @param null $tz
 * @return mixed
 */
function format_date($date, $format = null, $tz = null)
{
    if (is_null($date)) {
        return Carbon::now();
    }

    return $date->copy()->tz($tz)->format($format);
}

function convert_time_zone($data, $format = null, $tz = null)
{
    $userTime = new DateTime($data, new DateTimeZone('UTC'));
    $userTime->setTimezone(new DateTimeZone($tz));
    return $userTime->format($format);
}

/**
 * Give file size in humna readable form.
 *
 * @param $bytes
 * @param int $decimals
 * @return string
 */
function human_file_size($bytes, $decimals = 2)
{
    $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

/**
 * Return timezone array for select box
 *
 * @return array
 */
function timezone_select()
{
    $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

    $timezone_offsets = [];
    foreach ($timezones as $timezone) {
        $tz = new \DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new \DateTime);
    }

    $timezone_list = [];
    foreach ($timezone_offsets as $timezone => $offset) {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate('H:i', abs($offset));

        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

        $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
    }

    return $timezone_list;
}

function delete_directory_contents($dir, $ignore = ['.gitignore'])
{
    if (false === file_exists($dir)) {
        return false;
    }

    /** @var SplFileInfo[] $files */
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        if ($fileinfo->isDir()) {
            if (false === rmdir($fileinfo->getRealPath())) {
                return false;
            }
        } else {
            if (in_array($fileinfo->getFilename(), $ignore)) {
                continue;
            }

            if (false === unlink($fileinfo->getRealPath())) {
                return false;
            }
        }
    }
}

/**
 * Turn array into object.
 *
 * @param array $array
 * @return object
 */
function array_to_object(array $array)
{
    foreach($array as $key => $value)
    {
        if(is_array($value))
        {
            $array[$key] = array_to_object($value);
        }
    }
    return (object)$array;
}

/**
 * Set count for total transcriptions. 4 per subject.
 * @param $count
 * @return mixed
 */
function transcriptions_total($count)
{
    return $count * 4;
}

/**
 * Return completed transcriptions count
 * @param $expeditionId
 * @return mixed
 */
function transcriptions_completed($expeditionId)
{
    $transcription = new Transcription();
    return $transcription->getCountByExpeditionId($expeditionId);
}

/**
 * Return percentage of completed transcriptions
 * @param $total
 * @param $completed
 * @return float|int
 */
function transcriptions_percent_completed($total, $completed)
{
    return ($total == 0 || $completed == 0) ? 0 : ($completed / $total) * 100;
}