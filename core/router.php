<?php

/**
 * Gladiatus rewrite
 * 
 * @author Dracovian (Github)
 * @author KimChoJapFan (Ragezone)
 * 
 * @license 0BSD
 */

namespace Gladiatus\Core;

#[\Attribute] class Router { 
    public function __construct(
        private string $method = 'GET',
        private string $path = '/'
    ){}
}
