<?php

/**
 * Gladiatus rewrite
 * 
 * @author Dracovian (Github)
 * @author KimChoJapFan (Ragezone)
 * 
 * This Base32 implementation was taken from ChristianRiesen's implementation:
 *   https://github.com/ChristianRiesen/base32/blob/master/src/Base32.php
 * 
 * @license 0BSD
 *
 */

namespace Gladiatus\Core\OTP;

/**
 * Encode the input using base-32 encoding.
 * 
 * @param string $text  - The input text data to be encoded.
 */

class Base32 {

    public ?string $value = null;

    /// Store the 32 possible characters that the base-32 encoding can output.

    private string $base32_charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';
 
    private function _base32_encode(string $text) {

        /// Initialize our base-32 string with an empty string.
        
        $this->value ??= "";

        /**
         * Initialize our local function variables.
         * 
         * @var integer $scanner_width  - The current number of bits being stored prior to the pivot point in our input string.
         * @var integer $scanner_index  - The current byte location within the input string.
         * @var integer $scanner_pos    - The current character being encoded.
         */
        
        $scanner_width = 0;
        $scanner_index = 0;
        $scanner_pos = 0;

        /**
         * @var integer $text_length  - The number of characters in the unmodified input string.
         * @var string  $text         - Modify the input string by appending four null bytes to maintain 
         *                               divisibility by the width of a base-32 character (5 bits).
         */

        $text_length = strlen($text);
        $text .= str_repeat(chr(0), 4);

        /** @var array $char_array  - An array of byte values for the modified input string. */

        $char_array = unpack('C*', $text);

        /**
         * Continue looping if the following conditions are met:
         * 
         * If the current byte location within the input string does not exceed the total length of the input string, or...
         *  If we are storing one or more bits prior to the pivot point in our input string.
         */

        while ($scanner_index < $text_length || $scanner_width !== 0) {

            /// If the current number of bits being stored is fewer than the width of a base-32 character...

            if ($scanner_width < 5) {

                /// Then proceed to the next character in our input string, and add the byte (8 bits) length to our number of bits.

                $scanner_pos <<= 8;
                $scanner_width += 8;

                /// Increment the current byte location to reflect the shift in our input string.

                $scanner_index++;

                /// Set the current character to be encoded to reflect the shift in our input string.

                $scanner_pos += $char_array[$scanner_index];
            }

            /// Define the pivot point in our input string as the first 5 bits of the stored bits.

            $scanner_pivot = $scanner_width - 5;

            /**
             * Enter this conditional branch if the following conditions are met:
             * 
             * If the current character is not in the input string, and...
             *  If we have exhausted the length of our input string minus an additional...
             *   character if we're storing more than a byte (8 bits) prior to the pivot point.
             */

            if ($scanner_pos === 0 && $text_length < $scanner_index - ($scanner_width > 8)) {

                /// Append a base-32 padding character.

                $this->value .= '=';
                
                /// Jump over the part of the code that adds a base-32 character to the encoded string.
                
                goto end;
            }

            /// Set the encoded string value to the current character in our input string with the number of bits in the pivot point removed from the end.

            $this->value .= $this->base32_charset[$scanner_pos >> $scanner_pivot];

        end: /// Remove the pivoted bits from the current character and remove the width of a base-32 character from our stored bits at our pivot point.

            $scanner_pos &= (1 << $scanner_pivot) - 1;
            $scanner_width -= 5;
        }
    }

    public function __construct(?string $text = null) {
        if (is_null($text) || strlen($text) === 0)
            return;

        $this->_base32_encode($text);
    }
}