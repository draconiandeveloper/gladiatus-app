<?php
namespace Gladiatus;
use Core\Database;

$db = new Core\Database($GLOBALS['dbms']);
if (!$db->is_open())
    $db->connect($_ENV['POSTGRES_HOST'], $_ENV['POSTGRES_PORT'], $_ENV['POSTGRES_DB'], $_ENV['POSTGRES_USER'], $_ENV['POSTGRES_PASSWORD']);

session_start();
$errors = [];

function handle_login(string $username, string $password, array $results) {
    if (!password_verify($password, $results[0]['passhash'])) {
        $errors[] = '<span id="errortext">Incorrect password!</span>';
        return;
    }

    $_SESSION = [
        'logged_in' => true,
        'user_id' => $results[0]['uid'],
        'access' => $results[0]['access'],
        'username' => $results[0]['username'],
    ];
}

if (filter_has_var(INPUT_POST, 'login')) {
    if (!filter_has_var(INPUT_POST, 'username'))
        $errors[] = '<span id="errortext">Username required!</span>';

    if (!filter_has_var(INPUT_POST, 'password'))
        $errors[] = '<span id="errortext">Password required!</span>';

    $username = filter_input(INPUT_POST, 'username');
    $password = filter_input(INPUT_POST, 'password');

    $query = [];
    $db->safe_query($query, 'SELECT * FROM users WHERE username=? LIMIT 1', [$username], DBFUNC_GET);

    if (count($query) > 0) handle_login($username, $password, $query);
    $errors[] = '<span id="errortext">Account does not exist!</span>';
}