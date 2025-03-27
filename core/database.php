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

namespace Gladiatus\Core;
require_once 'security.php';

/**
 * Define some global variables in lieu of an enumerator due
 *  to testing issues with the autoloader and namespaces
 * 
 * @global DBMS_MYSQL    Use this definition for defining MySQL as our DBMS.
 * @global DBMS_MARIADB  Use this definition for defining MariaDB as our DBMS.
 * @global DBMS_POSTGRES Use this definition for defining PostgreSQL as our DBMS.
 * @global DBMS_SQLITE   Use this definition for defining SQLite as our DBMS.
 * @global DBMS_MSSQL    Use this definition for defining Microsoft SQL Server as our DBMS.
 * 
 * @global DBLIB_SYBASE  Use this definition for defining that Microsoft SQL Server was linked with Sybase libraries.
 * @global DBLIB_FREETDS Use this definition for defining that Microsoft SQL Server was linked with FreeTDS libraries.
 * @global DBLIB_MSSQL   Use this definition for defining that Microsoft SQL Server was linked with Microsoft libraries.
 * 
 * @global DBFUNC_SET    Use this definition for defining that our SQL query will not be returning any table data.
 * @global DBFUNC_GET    Use this definition for defining that our SQL query will be returning table data.
 */

define('DBMS_MYSQL',    0);
define('DBMS_MARIADB',  0);
define('DBMS_POSTGRES', 1);
define('DBMS_SQLITE',   2);
define('DBMS_MSSQL',    3);

define('DBLIB_SYBASE',  0);
define('DBLIB_FREETDS', 1);
define('DBLIB_MSSQL',   2);

define('DBFUNC_GET', 0);
define('DBFUNC_SET', 1);

/**
 * The core database class that handles all of our database functionality.
 * 
 * @license 0BSD
 */

class Database {
    private int $dbms;
    private int $dblib;

    private bool $use_unix_socket;
    private bool $in_memory;

    private array $valid_params;
    private string $dsn;
    private ?\PDO  $conn;

    /**
     * A private class function that is used to check if there are any null values where non-null
     *  values should otherwise be when it comes to forming a PDO DSN string.
     * 
     * @param string  $host  The hostname or the filename (UNIX socket or local file) for the database.
     * @param integer $port  The port number that the DBMS server is using to listen for requests.
     * @param string $dbname The name of the database that we're wanting to connect to.
     * 
     * @return boolean Return a "true" if the given parameters are not null and are valid to the PDO DSN string.
     */

    private function _are_conn_params_valid(string $host, int $port, string $dbname) : bool {
        
        /**
         * Follow this branch if we're using MySQL or MariaDB as our DBMS.
         * 
         * @var result  Used to store an array of boolean values from the "is_null" function, "true" for when the value is null.
         * @return bool Determine if "true" is in the result array, if there are no "true" values then the function returns "true".
         */

        if ($this->dbms === DBMS_MYSQL) {
            $result = array_map(fn(string|int $param) => is_null($param), ($this->use_unix_socket) ? [$host, $dbname] : [$host, $port, $dbname]);
            return in_array(true, $result) === false;
        }

        /**
         * Follow this branch if we're using PostgreSQL as our DBMS.
         * 
         * @var result  The same as the branch above.
         * @return bool The same as the branch above.
         */

        if ($this->dbms === DBMS_POSTGRES) {
            $result = array_map(fn(string|int $param) => is_null($param), [$host, $port, $dbname]);
            return in_array(true, $result) === false;
        }

        /**
         * Follow this branch if we're using Microsoft SQL Server as our DBMS.
         * 
         * @var result  The same as the branch above.
         * @return bool The same as the branch above.
         */

        if ($this->dbms === DBMS_MSSQL) {
            $result = array_map(fn(string $param) => is_null($param), [$host, $dbname]);
            return in_array(true, $result) === false;
        }

        /**
         * Otherwise fallback to SQLite as our DBMS.
         * 
         * @return bool "True" for when we're storing the database in memory or when the hostname/filename is not null.
         */

        return ($this->in_memory) ? true : !is_null($host);
    }

    /**
     * The constructor function for the class that is called when the database is
     *  called using the "new" keyword and assigned to a variable.
     *
     * @param integer $dbms             The DBMS type that we'll be using: DBMS_MYSQL, DBMS_MARIADB, DBMS_POSTGRES, DBMS_SQLITE, DBMS_MSSQL
     * @param boolean $use_unix_socket  Set to "true" if we're not using a web hostname but a local UNIX socket file to connect to the database.
     * @param boolean $in_memory        Set to "true" if we're storing the SQLite database in memory.
     * @param integer $dblib            The library used to link to the MSSQL DBMS: DBLIB_MSSQL, DBLIB_SYBASE, DBLIB_FREETDS
     */

    function __construct(int $dbms, bool $use_unix_socket = false, bool $in_memory = false, int $dblib = DBLIB_MSSQL) {
        $mssql_prefix = ($dblib == DBLIB_MSSQL) ? 'mssql' 
            : (($dblib == DBLIB_FREETDS) ? 'dblib' : 'sybase');
        
        
        /// Set the private class methods to be used in other class functions.

        $this->dbms = $dbms;
        $this->dblib = $dblib;
        $this->in_memory = $in_memory;
        $this->use_unix_socket = $use_unix_socket;
        $this->conn = null;

        /// Build the PDO DSN string for each supported DBMS and fill an array with the valid PDO arguments matching the DSN string.

        switch ($dbms) {
            case DBMS_MYSQL:
            case DBMS_MARIADB:
                $this->dsn = ($use_unix_socket) ? 'mysql:unix_socket=%s;dbname=%s' : 'mysql:host=%s;port=%d;dbname=%s';
                $this->valid_params = ($use_unix_socket) ? ['host', 'dbname'] : ['host', 'port', 'dbname'];
                break;
            
            case DBMS_POSTGRES:
            default:
                $this->dsn = 'pgsql:host=%s;port=%d;dbname=%s';
                $this->valid_params = ['host', 'port', 'dbname'];
                break;

            case DBMS_SQLITE:
                $this->dsn = ($in_memory) ? 'sqlite::memory:' : 'sqlite:%s';
                $this->valid_params = ($in_memory) ? [] : ['host'];
                break;

            case DBMS_MSSQL:
                $this->dsn = $mssql_prefix . ':host=%s;dbname=%s';
                $this->valid_params = ['host', 'dbname'];
                break;
        }
    }

    /**
     * Determine if the database connection is open.
     *
     * @return boolean "True" if the database is open.
     */

    public function is_open() : bool {
        return !is_null($this->conn);
    }

    /**
     * Connect to the database using PDO.
     *
     * @param string  $host     The hostname or filename for the database server.
     * @param integer $port     The port number that the database is using to listen for requests.
     * @param string  $dbname   The name of the database to access.
     * @param string  $username The username to access the database.
     * @param string  $password The password to access the database.
     * 
     * @return boolean "True" if the connection was successful.
     */

    public function connect(?string $host, ?int $port = null, ?string $dbname = null, ?string $username = null, ?string $password = null) : bool {
        
        /// Do not attempt to overwrite the existing database connection.

        if ($this->is_open()) {
            if ($GLOBALS['debug']) echo '<pre><b>Database:</b> attempting to connect to the database when already connected.</pre>';
            return true;
        }

        /// Determine if we are missing any important function parameters for connecting to the database.
        
        if (!$this->_are_conn_params_valid($host, $port, $dbname)) {
            if ($GLOBALS['debug']) echo '<pre><b>Database:</b> necessary parameters are missing!</pre>';
            return false;
        }

        /// Create an empty array to store the parameter values after reflecting the array of valid parameter names.
        
        $valid_params = [];
        foreach ($this->valid_params as $param)
            $valid_params[] = $$param;

        /// Unpack the array of valid parameters to fill the variadic arguments for the sprintf function.

        $dsn = sprintf($this->dsn, ...$valid_params);

        /**
         * Define our PDO connection attributes.
         * 
         * @property ATTR_DEFAULT_FETCH_MODE Determines how the retrieved data is structured when accessing it through the backend.
         * @property ATTR_ERRMODE            Determines how to handle any errors that may occur within the database.
         * @property ATTR_ORACLE_NULLS       Determines how we should handle NULL values in the database, convert them to empty strings.
         * @property ATTR_EMULATE_PREPARES   Determines whether or not we should use prepared statements (only available for MySQL/MariaDB).
         * @property ATTR_PERSISTENT         Determines whether or not the database connection should be kept open when there are no active queries.
         */

        $options = [
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_ORACLE_NULLS       => \PDO::NULL_EMPTY_STRING,
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::ATTR_PERSISTENT         => true
        ];

        /// Set a private class variable to store the PDO connection.
        
        $this->conn = new \PDO($dsn, $username, $password, $options);
        return true;
    }

    /**
     * Perform an SQL query on the database in a safe manner.
     *
     * @param array   &$return  Create a reference to a variable that will store the database data in an array.
     * @param string  $query    The SQL query string with question marks ('?') in the place of user input data.
     * @param integer $function Determines whether our query is returning any data or not: DBFUNC_SET, DBFUNC_GET.
     * @param array   $params   The user input data organized by the database table column names.
     * 
     * @return boolean "True" if the SQL query was successfully made.
     */

    public function safe_query(array &$return, string $query, int $function = DBFUNC_SET, array $params=[]) : bool {
        
        /// We cannot make any SQL queries to a database without connecting to it beforehand.

        if (!$this->is_open()) {
            if ($GLOBALS['debug']) 
                echo '<pre><b>Database:</b> attempting to make queries on a disconnected database!</pre>';

            goto error_return;
        }

        /// Try to perform the SQL query, if it fails, then set our referenced variable and return a false value.

        try {
            $statement = $this->conn->prepare($query);
            $statement->execute($params);

            $return['rows'] = $statement->rowCount();
            if ($function === DBFUNC_GET) $return['data'] = $statement->fetchAll();
            return true;
        } catch (\Exception $ex) {
            if ($GLOBALS['debug']) 
                echo "<pre><b>Database:</b> $ex</pre>";
        }

    error_return:
        $return['rows'] = 0;
        return false;
    }
}