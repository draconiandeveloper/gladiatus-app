<?php
namespace Gladiatus;
define('GLAD_BACKEND', true);

require_once 'config.php';
require_once 'core/autoload.php';

switch ($_SERVER['REQUEST_URI']) {
    case '/views/2fa':
        require __DIR__ . '/views/totp-token.php';
        break;
}