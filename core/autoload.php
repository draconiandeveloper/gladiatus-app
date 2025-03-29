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

/**
 * Initialize our class autoloader function that will be used to simplify
 *  the process of importing all necessary classes in the project.
 * 
 * @param string $name An anonymous function parameter that stores the namespace and class names to be loaded.
 */

spl_autoload_register(function($name) {

    /// Convert our anonymous function parameter into an array that separates the namespace and class names.
    /// Remove the uppermost namespace named "Gladiatus" as it won't be needed.

    $array = explode('\\', $name);
    array_shift($array); // Remove first entry

    /// Convert the absolute file directory of the current file, and break it into an array of directories.
    /// Remove the lowermost directory that should equal the parent folder of this file, "core".

    $parray = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
    array_pop($parray);   // Remove final entry

    /// Build an absolute file path to the backend file that is storing the class that we're loading.
    /// Import the backend file if it exists in the generated absolute file path.

    $file = strtolower(implode(DIRECTORY_SEPARATOR, [...$parray, ...$array])) . '.php';
    if (file_exists($file)) include_once $file;
});
