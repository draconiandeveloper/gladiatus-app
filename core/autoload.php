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
require_once 'security.php';

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
    array_shift($array);

    /// Build an absolute file path to the backend file that is storing the class that we're loading.
    /// Import the backend file if it exists in the generated absolute file path.
    
    $file = sprintf("%s/%s.php", dirname(__DIR__), strtolower(implode(DIRECTORY_SEPARATOR, $array)));
    if (file_exists($file))
        include_once $file;
});
