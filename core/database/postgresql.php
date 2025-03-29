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
 * Connect to our PostgreSQL server via TCP.
 * 
 * @param string  $hostname - The IP address or URL associated with the target database.
 * @param integer $port     - The network port number that the database is listening on.
 * @param string  $username - The user name to access the database.
 * @param string  $password - The user password to access the database.
 * @param string  $dbname   - The name of the database that we are connecting to.
 */

class PostgreSQL extends Database {
    
    public function __construct(string $hostname, ?int $port = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $dbname = null) {
        parent::__construct('pgsql', $hostname, $port, $dbname, false, $username, $password);
    }
}