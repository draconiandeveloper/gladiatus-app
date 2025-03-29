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
 * Extend the base PDO class to add our own functions.
 * 
 * @param string  $dbms     - The DataBase Management System that's hosting our database data.
 * @param string  $hostname - The IP address or URL associated with the target database.
 * @param integer $port     - The network port number that the database is listening on.
 * @param string  $dbname   - The name of the database that we are connecting to.
 * @param boolean $use_tcp  - True for TCP connections (IP or URL), False for UNIX socket connections.
 * @param string  $username - The user name to access the database.
 * @param string  $password - The user password to access the database.
 */

class Database extends \PDO {

    /// The public value to be accessed outside the class.

    public ?\PDO $value = null;

    /**
     * Build the Data Source Name for our database that uses UNIX sockets for communicating without TCP overhead.
     *
     * @param string $dbms      - The DataBase Management System that's hosting our database data.
     * @param string $filename  - The direct file path for our UNIX socket file.
     * @param string $dbname    - The name of the database that we are connecting to.
     * 
     * @return string|null      The Data Source Name for our particular Database Management System.
     */
    
    private function _build_dsn_socket(string $dbms, ?string $filename = null, ?string $dbname = null) : ?string {
        if ($dbms !== 'sqlite' && is_null($filename))
            return null;
        
        switch (strtolower($dbms)) {
        case 'mysql':
            return "mysql:unix_socket={$filename};dbname={$dbname}";
        
        case 'pgsql':
            return "pgsql:host={$filename};dbname={$dbname}";
        
        case 'sqlite':
            return is_null($filename) ? 'sqlite::memory:' : "sqlite:{$filename}";

        default:
            break;
        }

        return null;
    }

    /**
     * Build the Data Source Name for our database that uses TCP for communicating.
     *
     * @param string  $dbms      - The DataBase Management System that's hosting our database data.
     * @param string  $hostname  - The IP address or URL associated with the target database.
     * @param integer $port      - The network port number that the database is listening on.
     * @param string  $dbname    - The name of the database that we are connecting to.
     * 
     * @return string|null       The Data Source Name for our particular Database Management System.
     */

    private function _build_dsn_host(string $dbms, string $hostname, ?int $port = null, ?string $dbname = null) : ?string {
        if ($dbms === 'sqlite')
            return _build_dsn_socket($dbms, $hostname);

        switch (strtolower($dbms)) {
        case 'mysql':
            $port ??= 3306;
            return "mysql:host={$hostname};port={$port};dbname={$dbname}";
        
        case 'pgsql':
            $port ??= 5432;
            return "pgsql:host={$hostname};port={$port};dbname={$dbname}";
        
        default:
            break;
        }

        return null;
    }

    /**
     * Function to execute SQL queries in a safe manner.
     *
     * @param string $query   - The SQL query to be executed.
     * @param array  $params  - An array of values to be filtered.
     * 
     * @return array          The results of the SQL query.
     */

    public function run(string $query, ?array $params = null) : array {
        try {
            $prepared = parent::prepare($query);
            $prepared->execute($params ?? []);
        
            return [ 'count' => $prepared->rowCount(),
                     'data'  => $prepared->fetchAll() ];
        } catch (\BaseException) { 
            return [ 'count' => 0, 
                     'data'  => null ]; 
        }
    }

    public function __construct(string $dbms, string $hostname, ?int $port = null, ?string $dbname = null, bool $use_tcp = true, ?string $username = null, #[\SensitiveParameter] ?string $password = null) {
        $dsn = ($use_tcp) 
            ? $this->_build_dsn_host($dbms, $hostname, $port, $dbname) 
            : $this->_build_dsn_socket($dbms, $hostname, $dbname);

        $options = [
            parent::ATTR_DEFAULT_FETCH_MODE => parent::FETCH_ASSOC,
            parent::ATTR_EMULATE_PREPARES   => false,
            parent::ATTR_ORACLE_NULLS       => parent::NULL_EMPTY_STRING,
            parent::ATTR_PERSISTENT         => true,
            parent::ATTR_ERRMODE            => parent::ERRMODE_EXCEPTION,
            parent::ATTR_TIMEOUT            => 10
        ];
        
        try { $this->value ??= parent::__construct($dsn, $username, $password, $options); }
        catch (\BaseException) { return; }
    }
}