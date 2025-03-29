<?php

/**
 * This file exists solely as a way to exemplify how each core function can be utilized.
 * 
 * @author Dracovian (Github)
 * @author KimChoJapFan (Ragezone)
 * 
 * @license 0BSD
 */

namespace Gladiatus;
define('GLAD_BACKEND', true);

require_once 'config.php';
require_once 'core/autoload.php';

use Core\Database\PostgreSQL;
use Core\Database\Redis;
use Core\OTP\TOTP;
use Core\Template;

/// Connect to our PostgreSQL database with our environment variables.

$pgsql = new Core\Database\PostgreSQL(
    $_ENV['POSTGRES_HOST'],
    $_ENV['POSTGRES_PORT'],
    $_ENV['POSTGRES_USER'],
    $_ENV['POSTGRES_PASSWORD'],
    $_ENV['POSTGRES_DB']
);

/// Generate a Time-based One-Time Token.

$otp = new Core\OTP\TOTP('TestKey123456789');

/// Showcase simple templating.

$qrcodedata = <<<HTML
<div id="qrcode"></div><br>
        <pre>{% token %}</pre>

        <script type="application/javascript">
            const qrcode = new QRCode(document.querySelector('#qrcode'), {
                text: "{% otpauth %}",
                width: 512,
                height: 512,
                colorDark: '#000000',
                colorLight: '#FFFFFF',
                correctLevel: QRCode.CorrectLevel.H
            });
        </script>
HTML;

$qrcode = new Core\Template($qrcodedata, [
    'token' => $otp->value,
    'otpauth' => $otp->otpauth
]);

/// Showcase nested templating.

$htmldata = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <script type="application/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <title>{% title %}</title>
    </head>
    <body>
        {% qrcode %}
    </body>
</html>
HTML;

$htmlcode = new Core\Template($htmldata, [
    'title' => 'Core Functionality Example',
    'qrcode' => $qrcode->value,
]);

echo $htmlcode->value;

/// Let's exemplify our Redis database.

$redis = new Core\Database\RedisSock('unix:///run/redis.sock');

$redis->value->del('hashset');
$redis->value->hSet('hashset', 'my-key', 'Copyleft 2025 - Footer');
echo '<pre>' . $redis->value->hGet('hashset', 'my-key') . '</pre>';