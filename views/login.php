<?php

namespace Gladiatus;
use Core\Database\PostgreSQL;
use Core\Router;

#[Router('POST', '/login')]
class LoginPostController extends Core\Database\PostgreSQL {
    private array $errors = [];

    private function _handle_login(string $username, string $password) {
        $results = parent::run('SELECT * FROM users WHERE username=? LIMIT 1', [$username]);

        if ($results['count'] === 0) {
            $this->errors[] = 'No account found with that username!';
            return;
        }

        if (!password_verify($password, $results['data'][0]['passhash'])) {
            $this->errors[] = 'Incorrect password!';
            return;
        }

        if (!array_key_exists($username, $_SESSION)) {
            $_SESSION[$username] = [
                'logged_in' => true,
                'user_id' => $results['data'][0]['uid'],
                'access' => $results['data'][0]['access'],
            ];

            return;
        }

        if (!$_SESSION[$username]['logged_in']) {
            $_SESSION[$username]['logged_in'] = true;
            return;
        }
    }

    public function __construct() {
        parent::__construct(
            $_ENV['POSTGRES_HOST'],
            $_ENV['POSTGRES_PORT'],
            $_ENV['POSTGRES_USER'],
            $_ENV['POSTGRES_PASSWORD'],
            $_ENV['POSTGRES_DB']
        );
    }

    public function __invoke() {
        if (filter_has_var(INPUT_POST, 'login')) {
            $username = filter_input(INPUT_POST, 'username');
            $password = filter_input(INPUT_POST, 'password');

            $this->_handle_login($username, $password);

            foreach ($this->errors as $error)
                echo "<pre>{$error}</pre><br>";

            return <<<HTML
            <form method="POST" action="/login">
                <label for="username">Username</label>
                <input type="text" name="username"><br>
                <label for="password">Password</label>
                <input type="password" name="password"><br>
                <input type="submit" name="login" value="Login">
            </form>
            HTML;
        }
    }
}

#[Router('GET', '/login')]
class LoginGetController {
    public function __invoke() {
        return <<<HTML
        <form method="POST" action="/login">
            <label for="username">Username</label>
            <input type="text" name="username"><br>
            <label for="password">Password</label>
            <input type="password" name="password"><br>
            <input type="submit" name="login" value="Login">
        </form>
        HTML;
    }
}
