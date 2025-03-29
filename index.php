<?php
namespace Gladiatus;
define('GLAD_BACKEND', true);

switch ($_SERVER['REQUEST_URI']) {
    case '/login':
        require_once __DIR__ . '/views/login.php';
        break;
    case '/example':
        require_once __DIR__ . '/example.php';
        break;
}
