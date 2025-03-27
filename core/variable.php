<?php

namespace Gladiatus\Core;
require_once 'security.php';

class Variable extends \Redis {
    private array $options = [
        'host' => '',
        'port' => 0,
        'connectTimeout' => 2.5,
        'auth' => ['phpredis', 'phpredis'],
        'ssl' => [ 'verify_peer' => false ],
        'backoff' => [
            'algorithm' => \Redis::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
            'base' => 500,
            'cap' => 750
        ]
    ];

    private bool $is_conn = false;

    function __construct($hostname, $port = 6379, $use_socket = false) {
        $this->options['host'] = $hostname;
        $this->options['port'] = $port;

        if (!$this->is_conn) {
            parent::__construct($this->options);
            $this->is_conn = ($use_socket) 
                ? $this->pconnect($hostname) 
                : $this->pconnect($hostname, $port);
        }
    }

    function __destruct() {
        if ($this->is_conn) {
            $this->is_conn = false;
            parent::__destruct();
        }
    }

    public function is_open() : bool {
        return $this->is_conn;
    }

    /**
     * Strings:
     * @method append  - Append a value to a key
     * @method getSet  - Set a new value and return the old value
     * @method set     - Set the string of a key
     * @method strLen  - Get the length of the value stored in a key
     * 
     * Keys:
     * @method exists  - Determine if a key exists
     * @method pexpire - Set the expiration for a key using UNIX epoch
     * @method keys    - Find all keys matching the given pattern
     * @method rename  - Rename a key
     * 
     * Hashes:
     * @method hDel    - Delete one or more hash fields
     * @method hSet    - Set the string value of a hash field
     * @method hExists - Determine if a hash field exists
     * @method hGet    - Get the value of a hash field
     * @method hGetAll - Get all the fields and values in a hash
     * @method hKeys   - Get all the fields in a hash
     * @method hLen    - Get the number of fields in a hash
     * @method hVals   - Get all the values in a hash
     * @method hScan   - Scan a hash key for members
     * @method hStrLen - Get the length of the value associated with the field in the hash
     */
}