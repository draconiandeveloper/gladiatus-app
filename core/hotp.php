<?php

/**
 * Gladiatus rewrite
 * 
 * @author Dracovian (Github)
 * @author KimChoJapFan (Ragezone)
 * 
 * @license 0BSD
 *
 */

namespace Gladiatus\Core;

/// A neat little trick that I stole from MyBB's code that is used to prevent any attempted access to the backend files from the frontend.

if (!defined('GLAD_BACKEND')) {
    http_response_code(404);
    die('File not found.');
}

/**
 * The HMAC-based One-Time Password algorithm class
 * 
 * 
 */

class HOTP {

    /**
     * Pack our counter variable into a hex string.
     *
     * @param integer $count The incrementing counter that determines when the HOTP token expires.
     * @return string        The hex string to be passed into our HASH HMAC function.
     */

    private function _pack(int $count) : string {

        /// Generate an array of zeroes equal to 1 byte.

        $cur = array_fill(0, 8, 0);

        /// Iterate through our array, pack our count value into a hex string for each value in our array.

        for ($i = 7; $i >= 0; $i--) {
            $cur[$i] = pack('C*', $count);
            $count >>= 8;
        }

        /// Combine our array into a string.

        $bin = implode($cur);

        /// Pad the output string with zeroes if it's less than the width of a byte.

        if (strlen($bin) < 8) $bin .= str_repeat(chr(0), strlen($bin) - 8);

        return $bin;
    }

    /**
     * Generate our HOTP token string.
     *
     * @param string $hash     The input hex string from our _pack function.
     * @param integer $length  The length of our HOTP token (defaults to 6).
     * @return integer         The HOTP token.
     */

    private function _hotp(string $hash, int $length = 6) : int {

        /// Initialize our HMAC array as an empty array.

        $hmac = [];

        /// Iterate through the input hex string, 2 bytes at a time. Store the integer value of the hex values.

        foreach (str_split($hash, 2) as $hex)
            $hmac[] = hexdec($hex);

        /// Determine the current offset with the last HMAC value, strip away the last 8 bits of that value.

        $offset = $hmac[count($hmac) - 1] & 0xF;

        /// Generate the HOTP code by going through the HMAC array and build a 32 bit integer value from the entries.

        $code = (
            ($hmac[$offset + 0] & 0x7F) << 24 |
            ($hmac[$offset + 1] & 0xFF) << 16 |
            ($hmac[$offset + 2] & 0xFF) <<  8 |
            ($hmac[$offset + 3] & 0xFF) <<  0
        );

        /// Return the HOTP code limited to the length of our HOTP token.

        return $code % pow(10, $length);
    }

    /**
     * Generate our HOTP token.
     *
     * @param string $key      The secret key
     * @param integer $count   The incrementing counter that determines when the HOTP token expires
     * @param integer $length  The length of the output token (defaults to 6 numbers).
     * @return void
     */

    public function generate_token(string $key, int $count = 0, int $length = 6) {
        $count = $this->_pack($count);
        $hash = hash_hmac('sha1', $count, $key);
        $code = $this->_hotp($hash, $length);

        $code = str_pad($code, $length, "0", STR_PAD_LEFT);
        $code = substr($code, ($length * -1));

        return $code;
    }
}