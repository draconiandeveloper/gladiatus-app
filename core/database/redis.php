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
 * Connect to our Redis server via TCP.
 * 
 * @param string  $hostname - The IP address or URL associated with the target database.
 * @param integer $port     - The network port number that the database is listening on.
 * @param string  $username - The user name to access the database.
 * @param string  $password - The user password to access the database.
 * @param string  $dbname   - The name of the database that we are connecting to.
 */

class Redis extends \Redis {

    public ?\Redis $value = null;

    public function __construct(string $hostname, ?int $port = null, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?string $dbname = null) {
        $username ??= 'phpredis';
        $password ??= 'phpredis';
        $port ??= 6379;

        $options = [
            'host'           => $hostname,
            'port'           => $port,
            'connectTimeout' => 5,
            'ssl'            => [ 'verify_peer' => false ],
            'backoff'        => [ 'base' => 500, 'cap' => 750,
            'algorithm'      => parent::BACKOFF_ALGORITHM_DECORRELATED_JITTER ],
            'auth'           => [ $username, $password ]
        ];

        parent::__construct($options);
        $this->pconnect($hostname, $port);
        $this->value = $this;
    }
}