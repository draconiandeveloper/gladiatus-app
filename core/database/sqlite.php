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

namespace Gladiatus\Core\Database;

/**
 * Connect to our SQLite database from either a local file or in memory.
 * 
 * @param string $filename - The absolute file path to our SQLite database. 
 *                           If null, then create an SQLite database in memory.
 */

class SQLite extends Database {
    
    public function __construct(?string $filename = null) {
        parent::__construct('sqlite', $filename);
    }
}