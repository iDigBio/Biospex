<?php

namespace App\Services\Hash;

class BcryptHasher extends BaseHasher implements HasherContract
{

    /**
     * Hash strength.
     *
     * @var int
     */
    public $strength = 8;

    /**
     * Salt length.
     *
     * @var int
     */
    public $saltLength = 22;

    /**
     * Hash string.
     *
     * @param  string $string
     * @return string
     */
    public function hash($string)
    {
        // Format strength
        $strength = str_pad($this->strength, 2, '0', STR_PAD_LEFT);

        // Create salt
        $salt = $this->createSalt();

        //create prefix; $2y$ fixes blowfish weakness
        $prefix = PHP_VERSION_ID < 50307 ? '$2a$' : '$2y$';

        return crypt($string, $prefix . $strength . '$' . $salt . '$');
    }

    /**
     * Check string against hashed string.
     *
     * @param  string $string
     * @param  string $hashedString
     * @return bool
     */
    public function checkhash($string, $hashedString)
    {
        return $this->slowEquals(crypt($string, $hashedString), $hashedString);
    }

}
