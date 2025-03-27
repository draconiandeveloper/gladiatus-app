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

namespace Gladiatus;

/// A neat little trick that I stole from MyBB's code that is used to prevent any attempted access to the backend files from the frontend.

if (!defined('GLAD_BACKEND')) {
    http_response_code(404);
    die('File not found.');
}

/// Remove the PHP version header from all server responses to obscure the use of any potentially vulnerable versions of PHP from the frontend.

header_remove('X-Powered-By');
