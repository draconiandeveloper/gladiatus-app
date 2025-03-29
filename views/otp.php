<?php

namespace Gladiatus;
use Core\Template;
use Core\OTP\TOTP;
use Core\Router;

const qrcodedata = <<<HTML
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

const htmldata = <<<HTML
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

#[Router('GET', '/2fa')]
class OTPGetController {
    private ?Core\OTP\TOTP $totp = null;

    public function __construct() {
        if (is_null($this->totp))
            $this->totp = new Core\OTP\TOTP('TestKey123456789');
    }

    public function __invoke() {
        $qrcode = new Core\Template(qrcodedata, [
            'token' => $this->totp->value,
            'otpauth' => $this->totp->otpauth,
        ]);

        $htmlcode = new Core\Template(htmldata, [
            'title' => 'Routing and Templating',
            'qrcode' => $qrcode->value,
        ]);

        return $htmlcode->value;
    }
}