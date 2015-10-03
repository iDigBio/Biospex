<?php

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
        implode('/', array_map('rawurlencode', $path_parts))
        ;
}

/**
 * Push messages to session.
 *
 * @param $key
 * @param $value
 */
function session_flash_push($key, $value)
{
    $values = \Session::get($key, []);
    $values[] = $value;
    \Session::flash($key, $values);
}

/**
 * Round up to an integer, then to the nearest multiple of 5
 * Used for scaling project page percent complete
 *
 * @param $n
 * @param int $x
 * @return float
 */
function round_up_five($n, $x = 5)
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
    return $date->copy()->tz($tz)->format($format);
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


/**
 * Generate password - helper function
 * From http://www.phpscribble.com/i4xzZu/Generate-random-passwords-of-given-length-and-strength
 *
 * @param int $length
 * @param int $strength
 * @return string
 */
function generate_password($length=9, $strength=4)
{
    $vowels = 'aeiouy';
    $consonants = 'bcdfghjklmnpqrstvwxz';
    if ($strength & 1) {
        $consonants .= 'BCDFGHJKLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels .= "AEIOUY";
    }
    if ($strength & 4) {
        $consonants .= '23456789';
    }
    if ($strength & 8) {
        $consonants .= '@#$%';
    }

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}
