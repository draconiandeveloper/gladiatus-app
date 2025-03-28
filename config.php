<?php

/*
 * Gladiatus rewrite
 * 
 * @author Dracovian (Github)
 * @author KimChoJapFan (Ragezone)
 * 
 * @license 0BSD
 *
 */

namespace Gladiatus;

/// A neat little trick that I stole from MyBB's code that is used to prevent any attempted access to the backend files from the frontend.

if (!defined('GLAD_BACKEND')) {
    http_response_code(404);
    die('File not found.');
}

/// Remove the PHP version header from all server responses to obscure the use of any potentially vulnerable versions of PHP from the frontend.

header_remove('X-Powered-By');

/// Though we really shouldn't be using superglobals, this is a nicety that we can keep track of quite readily.

$GLOBALS['debug'] = true;

/// Disable error reporting if debug mode is enabled.

ini_set('display_errors', $GLOBALS['debug']);

/// Configure sessions to use Redis.

ini_set('session.save_handler', 'redis');
ini_set('redis.session.compression', 'zstd');
ini_set('redis.session.compression_level', 5);
ini_set('session.save_path', 'unix:///run/redis.sock?persistent=1&weight=1&database=0');

