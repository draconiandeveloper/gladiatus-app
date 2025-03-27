<?php

namespace Gladiatus;
define('GLAD_BACKEND', true);

require_once 'config.php';
require_once 'core/autoload.php';

use Core\Database;
use Core\Template;

$db = new Core\Database(DBMS_POSTGRES);

if (!$db->is_open())
    $db->connect('172.20.0.2', 5432, 'gladiatus', 'dracovian', 'Onomatopoeia');

$template = new Core\Template($db, 0, [
    'test-span' => 'Working',
    'span-another' => 'Another one',
]);

if (filter_has_var(INPUT_POST, 'savecode')) {
    $codename = filter_input(INPUT_POST, 'savename');
    $codedata = filter_input(INPUT_POST, 'code');

    if (!Core\Template::create($db, 0, $codename, $codedata)) {
        die('Uh oh');
    } else {
        die('Success!');
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Gladiatus Rewrite</title>
        <script type="application/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.39.1/ace.min.js" charset="utf-8"></script>
        <style type="text/css" media="screen">
            #editor {
                width: 400px;
                height: 400px;
                resize: both;
            }

            input {
                width: inherit;
            }
        </style>
    </head>
    <body>
        <div id="editor"></div><br>
        <input type="text" name="savename">
        <button id="savecode" disabled>Save Code</button>

        <?php
        echo $template('span-another');
        ?>

        <script type="application/javascript">
            const editor = ace.edit('editor');
            editor.session.setMode('ace/mode/php');
            editor.setTheme('ace/theme/github_dark');

            const savebtn = document.querySelector("#savecode");
            const savenam = document.querySelector("input[name=savename]");
            const edit = document.querySelector("#editor");

            var changes = [false, false];

            function check_changes() {
                if (changes[0] === true && changes[1] === true)
                    savebtn.removeAttribute('disabled');
            }

            edit.addEventListener('input', _ => { 
                if (!changes[0]) changes[0] = true;
                check_changes();
            });

            savenam.addEventListener('input', _ => {
                if (!changes[1]) changes[1] = true;
                check_changes();
            });

            savebtn.addEventListener('click', async _ => {
                try {
                    const options = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            savecode: true,
                            savename: savenam.value,
                            code: editor.getValue(),
                        })
                    };

                    const response = await fetch('/index.php', options);
                    console.log(await response.text());
                } catch {
                    console.log('uh oh');
                }
            });
        </script>
    </body>
</html>