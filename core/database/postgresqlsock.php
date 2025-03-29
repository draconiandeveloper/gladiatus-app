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
 * Connect to our PostgreSQL server via UNIX socket files.
 * 
 * @param string $filename - The absolute filepath for the UNIX socket associated with the target database.
 * @param string $username - The user name to access the database.
 * @param string $password - The user password to access the database.
 * @param string $dbname   - The name of the database that we are connecting to. 
 */

class PostgreSQLSock extends Database {

    public function __construct(string $filename, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $dbname = null) {
        parent::__construct('pgsql', $filename, null, $dbname, true, $username, $password);
    }
}