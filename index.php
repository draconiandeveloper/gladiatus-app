<?php

namespace Gladiatus;

define('GLAD_BACKEND', true);
require_once 'config.php';
require_once 'core/autoload.php';
use Core\Variable;

$vars = new Core\Variable('unix:///run/redis.sock', 0, true);

$vars->del('h');
$vars->hSet('h', 'key1', 'hello');
echo $vars->hGet('h', 'key1');
