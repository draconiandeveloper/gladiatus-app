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

namespace Gladiatus\Core\OTP;

/**
 * The Hash-Based Message Authentication Code-based One-Time Password algorithm (HMAC-OTP)
 * 
 * @param integer $counter       - The number of iterations that have elapsed.
 * @param integer $token_length  - The string length of the generated token.
 * @param string  $key           - The secret 16 byte long checksum key.
 */

class HOTP {
    public ?string $value = null;
    private ?int $length  = null;
    private ?int $counter = null;

    /// Store our secret key value in a manner where inheriting classes can still access the secret key value.

    protected ?string $key = null;

    /**
     * Convert the current counter value from little endian to big endian for transferring over a network.
     *
     * @return string The reversed and byte packed counter value.
     */

    private function _counter_bigendian() : string {

        /// Create an array of 8 null values.

        $counter_array = array_fill(0, 8, 0);

        /// Copy the current counter value to make modifications without messing up the original counter.

        $counter_copy = $this->counter;

        /// Iterate through all 8 null values in our array, fill them with the byte packed counter.

        for ($i = 7; $i >= 0; $counter_copy >>= 8, $i--)
            $counter_array[$i] = pack('C*', $counter_copy);

        /// Join the array into a string value without a delimiter.

        $counter_big = implode($counter_array);

        /// Take the string length of our joined array, remove the length of a single byte (8 bits) from the length;

        $counter_len = strlen($counter_big) - 8;

        /// If the length of our counter length is below 0, then pad the big endian counter string with enough null bytes to bring the counter length to 0.

        if ($counter_len < 0)
            $counter_big .= str_repeat(chr(0), -$counter_len);

        return $counter_big;
    }

    /**
     * Extract 31 bits from the SHA-1 hash of our big endian counter.
     *
     * @param string $counter_big  - The big endian counter value from the function above.
     * 
     * @return integer             Return the extracted 31 bits from our big endian counter value checksum.
     */
    private function _extract_31bits(string $counter_big) : ?int {

        /// We require the secret 16 bytes to be defined 

        if (!array_key_exists('secret-key', $GLOBALS) && is_null($this->key))
            return null;

        /// Either use the 16 bytes given or the pre-defined secret key.

        $this->key ??= $GLOBALS['secret-key'];

        /// Perform a SHA-1 hash with our key as the checksum.

        $hhmac = hash_hmac('sha1', $counter_big, $this->key);

        /// Create an empty array to store the byte (8 bits) of the hash.

        $truncated = [];
        foreach (str_split($hhmac, 2) as $hex)
            $truncated[] = hexdec($hex);

        /// Retrieve the last value in our array from above, remove the last 4 bits from the last value.

        $offset = $truncated[count($truncated) - 1] & 15;

        /// Retrieve the array values at the location equal the the 4 bits from above onwards to skip the last 4 bits from the hash.

        $extracted = (
            ($truncated[$offset + 0] & 127) << 24 | /// Take a byte, strip away the upper half, move it to the first 4 bits of the extracted data.
            ($truncated[$offset + 1] & 255) << 16 | /// Take the next byte, move it to the next 8 bits of the extracted data.
            ($truncated[$offset + 2] & 255) <<  8 | /// Take the next byte, move it to the next 8 bits of the extracted data.
            ($truncated[$offset + 3] & 255) <<  0   /// Take the last byte, move it to the last 8 bits of the extracted data.
        );

        return $extracted;
    }

    public function __construct(int $counter, int $token_length = 6, ?string $key = null) {

        /// Set the length to 10 to the power of the token length and initialize the current counter.

        $this->length ??= pow(10, $token_length);
        $this->counter ??= $counter;
        $this->key = $key;

        /// Convert the little endian counter value to its big endian counterpart.

        $counter_big = $this->_counter_bigendian();

        /// Extract the final 31 bits from the SHA-1 hash of our big endian counter value.

        $extracted = $this->_extract_31bits($counter_big);

        /// Divide the hash value above by the length and return the remainder.

        $extracted %= $this->length;

        /// Prepend zeroes to the start of our remainder above to produce a string that is as long or longer than the given token length.

        $extracted = str_pad($extracted, $token_length, '0', STR_PAD_LEFT);

        /// Strip away any extra padding characters from the token.

        $this->value = substr($extracted, -$token_length);
    }
}