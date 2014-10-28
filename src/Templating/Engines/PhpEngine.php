<?php
/*!
 * Radium
 * Copyright 2011-2014 Jack Polgar
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Radium\Templating\Engines;

use Exception;
use Radium\Templating\EngineInterface;
use Radium\Language;

/**
 * PHP "template" renderer.
 *
 * @since 2.0.0
 * @author Jack Polgar <jack@polgar.id.au>
 */
class PhpEngine implements EngineInterface
{
    /**
     * Paths to search for templates in.
     *
     * @var string[]
     */
    protected $paths = [];

    /**
     * Global variables.
     *
     * @var array
     */
    protected $globals = array();

    /**
     * @return string
     */
    public function name()
    {
        return 'php';
    }

    /**
     * Adds a global variable for all templates.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * Adds a template path to search in.
     *
     * @param string|array $path
     */
    public function addPath($path, $prepend = false)
    {
        if (is_array($path)) {
            foreach ($path as $directory) {
                $this->addPath($directory, $prepend);
            }
        } else {
            if ($prepend) {
                $this->paths = array_merge([$path], $this->paths);
            } else {
                $this->paths[] = $path;
            }
        }
    }

    /**
     * @param string $template
     * @param array  $locals
     *
     * @return string
     */
    public function render($template, array $locals = [])
    {
        $templatePath = $this->find($template);

        if (!$templatePath) {
            throw new Exception("Unable to find template [$template]");
        }

        // View variables
        $variables = $locals + $this->globals;
        foreach ($variables as $_name => $_value) {
            $$_name = $_value;
        }

        // Shortcut for escaping HTML
        $e = function($string) {
            return htmlspecialchars($string);
        };

        // Shortcut for Language::translate()
        $t = function($string, array $vars = array()) {
            return Language::translate($string, $vars);
        };

        // Shortcut for Language::date()
        $l = function($format, $timestamp = null) {
            return Language::date($format, $timestamp);
        };

        ob_start();
        include($templatePath);
        return ob_get_clean();
    }

    /**
     * Checks if the engine can render the template.
     *
     * @param string $template
     *
     * @return bool
     */
    public function supports($template)
    {
        $extension = pathinfo($template, \PATHINFO_EXTENSION);
        return in_array($extension, ['php', 'phtml']);
    }

    /**
     * Checks if the template exists.
     *
     * @param string $template
     *
     * @return bool
     */
    public function exists($template)
    {
        return $this->find($template) ? true : false;
    }

    /**
     * Searches for the template in the registered search paths.
     *
     * @param string $template
     *
     * @return string|bool
     */
    public function find($template)
    {
        if (!$this->supports($template)) {
            return false;
        }

        foreach ($this->paths as $path) {
            $filePath = "{$path}/{$template}";

            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        return false;
    }
}
