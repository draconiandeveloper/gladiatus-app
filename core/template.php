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

/**
 * Make a simple templating system that can be used to minify each page with nested templates.
 * 
 * @param string $template_data    - The template data with the templated variables included.
 * @param array  $template_values  - An array of key-value pairs where the keys are the same as the templated
 *                                    variables, and the values are what replaces the templated variables.
 */

class Template {

    public ?string $value = null;

    public function __construct(string $template_data, ?array $template_values = null) {

        /// No template values, then we just simply return the unchanged template data.

        if (is_null($template_values)) {
            $this->value = $template_data;
            return;
        }

        /// Iterate through the template values, replace all templated variables accordingly.

        foreach ($template_values as $key => $value)
            $template_data = str_replace("{% $key %}", $value, $template_data);
        
        /// Store our "rendered" template data where we can readily access it.

        $this->value ??= $template_data;
    }
}