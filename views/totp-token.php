<?php
namespace Gladiatus;
require_once 'config.php';
require_once 'core/autoload.php';

use Core\Base32;
use Core\TOTP;

$b32 = new Core\Base32($GLOBALS['secret']);
$tfa = new Core\TOTP();

$token = $tfa->generate($GLOBALS['secret']);
$qrcode = $tfa->qrcode_uri($b32->encoded, 'Gladiatus');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>TOTP Test</title>
        <script type="application/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    </head>
    <body>
        <div id="qrcode"></div>

        <script type="application/javascript">
            const qrcode = new QRCode(document.querySelector('#qrcode'), {
                text: "<?= $qrcode ?>",
                width: 512,
                height: 512,
                colorDark: '#000000',
                colorLight: '#FFFFFF',
                correctLevel: QRCode.CorrectLevel.H
            });
        </script><br>
        <div><?= $token ?></div>
    </body>
</html>