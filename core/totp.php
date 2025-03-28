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
 * HOTP Specification:
 * 
 * K = Secret Key (SHA-1 HMAC of 16 bytes in length)
 * H = Hashing algorithm (SHA-1)
 * C = Counter (counts iterations and must be big endian)
 * d = HOTP token length (default of 6)
 * i = Index used to select 31 bits from MAC
 * 
 * HOTP value = HOTP(K, C) mod POW(10, d)
 * HOTP(K, C) = TRUNCATE(HMAC_H(K, C))
 * TRUNCATE(MAC) = EXTRACT31(MAC, MAC[(19 * 8 + 4) : (19 * 8 + 7)])
 * EXTRACT31(MAC, i) = MAC[(i * 8 + 1) : (i * 8 + 4 * 8 - 1)]
 * 
 * TOTP Specification:
 * 
 * T0 = The UNIX time from which to start counting time steps (default 0)
 * TX = The interval at which to calculate the value of the counter (default 30)
 * CT = The maximum counter duration (default 30 seconds)
 * T  = Current UNIX time
 * 
 * TOTP value(K) = HOTP value(K, CT)
 * CT = (T - T0 / TX)
 */

class TOTP {

    /// Store the duration of each TOTP token (30 second by default).

    private int $timeInterval;

    /// Store the length of our generated token (default 6).

    private int $tokenLength;

    /// Store the starting time of our genrated token (default 0).

    private int $startTime;

    /**
     * The class constructor/entrypoint.
     * 
     * @param integer $expires The number of seconds before a TOTP token expires.
     * @param integer $length  The length of our generated TOTP token.
     * @param integer $start   The starting time for our generated token.
     */

    function __construct(int $expires = 30, int $length = 6, int $start = 0) {
        $this->timeInterval = $expires;
        $this->tokenLength = $length;
        $this->startTime = $start;
    }

    /**
     * Convert our counter from little endian to big endian.
     *
     * @param integer $count Our little-endian counter value. 
     * @return string        The big-endian representation of our counter value.
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
     * @return integer         The HOTP token.
     */

    private function _hotp(string $hash) : int {

        /// Initialize our HMAC array as an empty array.

        $hmac = [];

        /// Iterate through the input hex string, 2 bytes at a time. Store the integer value of the hex values.

        foreach (str_split($hash, 2) as $hex)
            $hmac[] = hexdec($hex);

        /// Determine the current offset with the last HMAC value, strip away the last 8 bits of that value.

        $offset = $hmac[count($hmac) - 1] & 0xF;

        /// Perform the EXTRACT31 function of the HOTP specification by removing the upper portion of our hex string.

        $code = (
            ($hmac[$offset + 0] & 0x7F) << 24 |
            ($hmac[$offset + 1] & 0xFF) << 16 |
            ($hmac[$offset + 2] & 0xFF) <<  8 |
            ($hmac[$offset + 3] & 0xFF) <<  0
        );

        /// Return the HOTP code limited to the length of our HOTP token.

        return $code % pow(10, $this->tokenLength);
    }

    /**
     * Generate our TOTP token from the HOTP key value.
     * 
     * @param string $key         The secret key.
     * @param null|integer $time  The current time, if null then we get the current UNIX epoch.
     * @return string             The TOTP token string.
     */

    function generate(string $key, ?int $time = null) : string {

        /// If our input time is null, then get the current UNIX timestamp instead.

        if (is_null($time)) $time = (new \DateTime())->getTimestamp();

        /// Determine how many seconds have elapsed since the token was generated.

        $count = floor(($time - $this->startTime) / $this->timeInterval);

        /// Pack that time into a binary string.

        $count = $this->_pack($count);

        /// Hash that time using the given algorithm.

        $hash = hash_hmac('sha1', $count, $key);

        /// Generate the HOTP token.

        $code = $this->_hotp($hash);

        /// Pad the token to fit our token length.

        $code = str_pad($code, $this->tokenLength, "0", STR_PAD_LEFT);

        /// Retrieve the last portion of our token starting to ensure that we are returning the correct length value.

        $code = substr($code, ($this->tokenLength * -1));
        return $code;
    }

    /**
     * Return a valid OTP QR code URI.
     * 
     * otpauth://totp/{Issuer}?secret={B32SECRET}&issuer={ISSUER}&algorithm=SHA1&digits={TOKENLEN}&period={TOKENTIME}
     * 
     * @param string $b32secret The base32 encoded secret TOTP key
     * @return string           The OTPAuth URI
     */

    public function otpauth_uri(string $b32secret) {
        return sprintf('otpauth://totp/Gladiatus?secret=%s&issuer=Gladiatus&algorithm=SHA1&digits=%d&period=%d', urlencode($b32secret), $this->tokenLength, $this->timeInterval);
    }
}