<?php

namespace Gladiatus;
define('GLAD_BACKEND', true);

require_once './config.php';
require_once './core/autoload.php';
require_once './views/views.php';

use Core\Router;
session_start();

const rootdata = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <title>Gladiatus Rewrite - Indev</title>
    </head>
    <body>
        <a href="/2fa">Two Factor Authentication Test</a><br>
        <a href="/login">Database Test</a>
    </body>
</html>
HTML;

#[Router('GET', '/')]
class RootController {
    public function __invoke() {
        return rootdata;
    }
}

$views = new Views();
$views(RootController::class);
$views(OTPGetController::class);
$views(LoginGetController::class);
$views(LoginPostController::class);
echo ($views->route())();
