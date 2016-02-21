<?php

namespace App\Services\Hash;

interface HasherContract
{

    /**
     * Hash string.
     *
     * @param  string $string
     * @return string
     */
    public function hash($string);

    /**
     * Check string against hashed string.
     *
     * @param  string $string
     * @param  string $hashedString
     * @return bool
     */
    public function checkhash($string, $hashedString);

}
