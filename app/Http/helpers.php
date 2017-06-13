<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

/**
 * @param $array
 * @return \Illuminate\Support\Collection
 */
function array_to_collection($array)
{
    foreach ($array as $key => $value)
    {
        if (is_array($value))
        {
            $value = array_to_collection($value);
            $array[$key] = $value;
        }
    }

    return collect($array);
}


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
 * Round up to an integer, then to the nearest multiple of 5.
 * Used for scaling project page percent complete.
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
    if (is_null($date))
    {
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
 * Give file size in human readable form.
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
 * Return timezone array for select box.
 *
 * @return array
 */
function timezone_select()
{
    $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

    $timezone_offsets = [];
    foreach ($timezones as $timezone)
    {
        $tz = new \DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new \DateTime);
    }

    $timezone_list = [];
    foreach ($timezone_offsets as $timezone => $offset)
    {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate('H:i', abs($offset));

        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";

        $timezone_list[$timezone] = "(${pretty_offset}) $timezone";
    }

    return $timezone_list;
}

/**
 * Delete directory contents.
 *
 * @param $dir
 * @param array $ignore
 * @return bool
 */
function delete_directory_contents($dir, array $ignore = ['.gitignore'])
{
    if (false === file_exists($dir))
    {
        return false;
    }

    /** @var SplFileInfo[] $files */
    $files = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo)
    {
        if ($fileinfo->isDir())
        {
            if (false === rmdir($fileinfo->getRealPath()))
            {
                return false;
            }
        }
        else
        {
            if (in_array($fileinfo->getFilename(), $ignore))
            {
                continue;
            }

            if (false === unlink($fileinfo->getRealPath()))
            {
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
    foreach ($array as $key => $value)
    {
        if (is_array($value))
        {
            $array[$key] = array_to_object($value);
        }
    }
    return (object) $array;
}

/**
 * Set count for total transcriptions. 4 per subject.
 *
 * @param $count
 * @return mixed
 */
function transcriptions_total($count)
{
    return (int) $count * 3;
}

/**
 * Return completed transcriptions count.
 *
 * @param $expeditionId
 * @return mixed
 */
function transcriptions_completed($expeditionId)
{
    $transcriptionContract = app(\App\Repositories\Contracts\PanoptesTranscriptionContract::class);

    return $transcriptionContract->setCacheLifetime(0)->getTranscriptionCountByExpeditionId($expeditionId);
}

/**
 * Return percentage of completed transcriptions.
 *
 * @param $total
 * @param $completed
 * @return float|int
 */
function transcriptions_percent_completed($total, $completed)
{
    $value = ($total === 0 || $completed === 0) ? 0 : ($completed / $total) * 100;

    return ($value > 100) ? 100 : $value;
}


/**
 * jTraceEx() - provide a Java style exception trace
 *
 * @param $e
 * @param array $seen - array passed to recursive calls to accumulate trace lines already seen.
 * @return array|string
 */
function jTraceEx($e, array $seen = [])
{
    $starter = $seen ? 'Caused by: ' : '';
    $result = array();

    $trace = $e->getTrace();
    $prev = $e->getPrevious();
    $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
    $file = $e->getFile();
    $line = $e->getLine();

    while (true)
    {
        $current = "$file:$line";
        if (is_array($seen) && in_array($current, $seen, true))
        {
            $result[] = sprintf(' ... %d more', count($trace) + 1);
            break;
        }

        $result[] = sprintf(' at %s%s%s(%s%s%s)',
            count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
            count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
            count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
            $line === null ? $file : basename($file),
            $line === null ? '' : ':',
            $line === null ? '' : $line);

        if (count($seen) > 0)
        {
            $seen[] = "$file:$line";
        }

        if ( ! count($trace))
        {
            break;
        }

        $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
        $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
        array_shift($trace);
    }
    $result = implode('<br />', $result);

    if ($prev)
    {
        $result .= '<br />' . jTraceEx($prev, $seen);
    }

    return $result;
}

/**
 * Check if table has index.
 *
 * @param $table
 * @param $index
 * @return bool
 */
function table_has_index($table, $index)
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
function decamelize($string)
{
    return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
}

/**
 * @param $input
 * @return mixed|null
 */
function get_state($input)
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
        'Wyoming'              => 'WY'
    ];

    foreach ($states as $name => $abbr)
    {
        if (strtolower($input) === strtolower($name))
        {
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
function camelCaseToWords($string)
{
    $split_data = preg_split('/(?=[A-Z])/', $string);

    return ucwords(implode(' ', $split_data));
}