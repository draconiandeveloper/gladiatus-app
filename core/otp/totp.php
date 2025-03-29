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
 * The Time-based One-Time Password (Time-based OTP).
 * 
 * @param string  $key           - The secret 16 byte checksum key.
 * @param integer $start_time    - The time to elapse before we start incrementing to our expiry time.
 * @param integer $token_length  - The string length of the generated token.
 * @param integer $token_expiry  - The number of iterations/seconds before the token is no longer valid.
 */

class TOTP extends HOTP {

    public ?string $otpauth = null;

    public function __construct(?string $key = null, int $start_time = 0, int $token_length = 6, int $token_expiry = 30) {

        /// Get the current time in the form of a UNIX epoch.

        $now = (new \DateTime())->getTimestamp();

        /// Retrieve the incremental counter value based on how many times we've had our tokens expire.

        $counter = floor(($now - $start_time) / $token_expiry);

        /// Call the Hash-Based Message Authentication Code-based One-Time Password algorithm constructor.

        parent::__construct($counter, $token_length, $key);

        /// Encode the secret key using base-32 encoding before making it safe to use as a URI parameter.

        $base32 = new Base32($this->key);
        $safe32 = urlencode($base32->value);

        /// Set the QR Code OTPAuth URL and fill in our mutable values.

        $this->otpauth = "otpauth://totp/Gladiatus?secret={$safe32}&issuer=Gladiatus&algorithm=SHA1&digits={$token_length}&period={$token_expiry}";
    }
}