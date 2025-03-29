<?php

namespace Gladiatus;
use Core\Router;

/// Our include block because we already have an autoloader present for the core classes.

include_once 'login.php';
include_once 'otp.php';

class Views {
    public function __construct(public array $routes = []) {}

    public function __invoke($class) {
        if (!in_array($class, $this->routes, true))
            $this->routes[] = $class;
    }

    public function route() {
        foreach ($this->routes as $handler) {
            $reflection = new \ReflectionClass($handler);
            $attributes = $reflection->getAttributes(Router::class);

            foreach ($attributes as $attribute) {
                if ($attribute->getArguments()[0] !== $_SERVER['REQUEST_METHOD'])
                continue;

                if ($attribute->getArguments()[1] !== $_SERVER['REQUEST_URI'])
                    continue;

                return (new $handler);
            }
        }
    }
}
