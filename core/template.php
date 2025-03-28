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

/// A neat little trick that I stole from MyBB's code that is used to prevent any attempted access to the backend files from the frontend.

if (!defined('GLAD_BACKEND')) {
    http_response_code(404);
    die('File not found.');
}

/// We will likely end up caching our template data in the database for long-term storage.

use Core\Variable;
use Core\Database;

/**
 * The core template class that handles all of the frontend HTML and CSS data.
 * 
 * @license 0BSD
 */

class Template {
    private array    $templ_cache;
    private array    $templ_vars;
    private int      $templ_id;
    private          $db;

    function __construct($db, int $template_id, ?array $variables) {
        $this->templ_vars = is_null($variables) ? [] : $variables;
        $this->templ_id = $template_id;
        $this->templ_cache = [];
        $this->db = $db;

        $this->load();
        $this->render();
    }

    function __invoke(string $name) : string {
        return $this->templ_cache[$name];
    }

    private function load() : bool {
        if (!$this->db->is_open()) {
            if ($GLOBALS['debug']) echo '<pre><b>Template:</b> cannot fetch template data from a closed database!</pre>';
            return false;
        }

        $results = [];
        if (!$this->db->safe_query($results, 'SELECT name, data, updated FROM templates WHERE gid=?', DBFUNC_GET, [$this->templ_id])) {
            if ($GLOBALS['debug']) echo "<pre><b>Template:</b> template ID {$this->templ_id} does not exist!</pre>";
            return false;
        }

        if ($results['rows'] === 0) {
            if ($GLOBALS['debug']) echo "<pre><b>Template:</b> template ID {$this->templ_id} does not exist!</pre>";
            return false;
        }

        for ($i = 0; $i < $results['rows']; $i++)
            $this->templ_cache[$results['data'][$i]['name']] = $results['data'][$i]['data'];
        
        return true;
    }

    private function render() {
        foreach ($this->templ_cache as $templ_name => $templ_data)
            $this->templ_cache[$templ_name] = str_replace("\{\% $templ_name \%\}", $templ_data, $this->templ_vars[$templ_name]);
    }

    public static function create(Database $db, int $gid, string $name, $data = null) : bool {
        if (!$db->is_open()) {
            if ($GLOBALS['debug']) echo '<pre><b>Template:</b> cannot create template data in a closed database!</pre>';
            return false;
        }

        $results = [];
        $safename = htmlspecialchars($name);

        if (!$db->safe_query($results, 'INSERT INTO templates (gid, name, data) VALUES (?, ?, ?)', DBFUNC_SET, [$gid, $name, $data])) {
            if ($GLOBALS['debug']) echo "<pre><b>Template:</b> template \"$safename\" was not added to the database!</pre>";
            return false;
        }

        if ($results['rows'] === 0) {
            if ($GLOBALS['debug']) echo "<pre><b>Template:</b> template \"$safename\" was not added to the database!</pre>";
            return false;
        }

        return true;
    }
}