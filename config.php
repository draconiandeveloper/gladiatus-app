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

/// Include the security core script to include the definition.

require_once 'core/security.php';

/// Though we really shouldn't be using superglobals, this is a nicety that we can keep track of quite readily.

$GLOBALS['debug'] = true;

/// Disable error reporting if debug mode is enabled.

ini_set('display_errors', $GLOBALS['debug']);

/// Configure sessions to use Redis.

ini_set('session.save_handler', 'redis');
ini_set('redis.session.compression', 'zstd');
ini_set('redis.session.compression_level', 5);
ini_set('session.save_path', 'unix:///run/redis.sock?persistent=1&weight=1&database=0');
