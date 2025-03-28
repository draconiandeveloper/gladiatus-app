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

namespace Gladiatus\Core;

/// A neat little trick that I stole from MyBB's code that is used to prevent any attempted access to the backend files from the frontend.

if (!defined('GLAD_BACKEND')) {
    http_response_code(404);
    die('File not found.');
}

/**
 *              The Base32 Alphabet (RFC-3548) Table:
 * 
 *              +-------+---------+-------+---------+
 *              | Char  | Binary  | Char  | Binary  |
 *              +-------+---------+-------+---------+
 *              |   A   |  00000  |   Q   |  10000  |
 *              |   B   |  00001  |   R   |  10001  |
 *              |   C   |  00010  |   S   |  10010  |
 *              |   D   |  00011  |   T   |  10011  |
 *              |   E   |  00100  |   U   |  10100  |
 *              |   F   |  00101  |   V   |  10101  |
 *              |   G   |  00110  |   W   |  10110  |
 *              |   H   |  00111  |   X   |  10111  |
 *              |   I   |  01000  |   Y   |  11000  |
 *              |   J   |  01001  |   Z   |  11001  |
 *              |   K   |  01010  |   2   |  11010  |
 *              |   L   |  01011  |   3   |  11011  |
 *              |   M   |  01100  |   4   |  11100  |
 *              |   N   |  01101  |   5   |  11101  |
 *              |   O   |  01110  |   6   |  11110  |
 *              |   P   |  01111  |   7   |  11111  |
 *              +-------+---------+-------+---------+
 * 
 *  When encoding data, we're taking a stream of bits and breaking it down
 *   from byte-width (8 bits) to a width of 5 bits and then associating
 *        the 5-bit chunks with the Base32 alphabet table above.
 * 
 *                           T = 01010100
 *                           e = 01100101
 *                           s = 01110011
 *                           t = 01110100
 * 
 *                      8-bit (byte) boundaries
 *                 01010100|01100101|01110011|01110100
 * 
 *               5-bit (Base32) boundaries with padding
 *             01010|10001|10010|10111|00110|11101|00 000
 * 
 *    Associating the bits of the 5-bit boundaries with the Base32 table
 *      we can gather the following characters for the encoded version.
 *                         
 *                            K = 01010
 *                            R = 10001
 *                            S = 10010
 *                            X = 10111
 *                            G = 00110
 *                            5 = 11101
 *                            A = 00000
 * 
 *     When we apply padding ('=') we do so to make the Base32 encoded
 *          string of a length that is evenly divisible by 8.
 * 
 *   So in this case we have 7 Base32 characters and as such we must add one more
 *      character to have 8 characters, so we add one pad character at the end 
 *                 of that and that should be our encoded string.
 * 
 *                         Test -> KRSXG5A=
 */

class Base32 {

    /// Our Base32 character set, or the list of valid characters.

    private string $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567=';

    /// The Base32 encoded string.

    public string $encoded = "";

    /**
     * Perform the Base32 encoding on an input string.
     *
     * @param string $text The input string to be encoded.
     */

    function __construct(string $text) {

        /// Initialize our local variables.

        $length = 0;
        $curchar = 0;
        $char = 0;

        /// Retrieve the length of our input and initialize our output string.

        $strlen = strlen($text);
        $result = "";

        /// Pad the input string to ensure that our null terminators are divisible by 5.

        $text .= str_repeat(chr(0), 4);

        /// Convert the input string into a string of hexadecimal characters.

        $chars = unpack("C*", $text);

        /// Loop through all of the input string characters.

        while ($curchar < $strlen || $length !== 0) {

            /*
             * If the bit length has fallen below 5 bits (width of Base32 character)
             *  then shift left by 8 bits and add the next character from the input
             */

            if ($length < 5) {
                $char <<= 8;
                $length += 8;

                $curchar++;
                $char += $chars[$curchar];
            }

            /// Create a variable to store the current shift point in the string. Set it to the width of the next Base32 character.

            $shift = $length - 5;
            
            /*
             * If the bit length is greater than 8 bits wide, then remove 1
             *  from the current character.
             * 
             * If that exceeds the length of the input string, then we will
             *  append a padding character ('=') to the encoded string.
             * 
             * Otherwise, we add the equivalent Base32 character to the
             *  encoded string.
             */
            
            $this->encoded .= ($char === 0 && $strlen < $curchar - ($length > 8)) ? '='
                : $this->charset[$char >> $shift];
            
            /*
             * Move the shift bits to the left by 1 bit and subtract 1 from that value.
             *  Check if each bit of the $char variable matches each bit of the shift.
             */

            $char &= (1 << $shift) - 1;
            
            /// Remove a single Base32 character from the overall length of our output.
            
            $length -= 5;
        }
    }
}