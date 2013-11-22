<?php
/*!
 * Radium
 * Copyright (C) 2011-2013 Jack P.
 * https://github.com/nirix
 *
 * This file is part of Radium.
 *
 * Radium is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; version 3 only.
 *
 * Radium is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Radium. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Radium\Output;

use Radium\Loader;
use Radium\Error;
use Radium\Exception;
use Radium\Language;

/**
 * Radium's View rendering class.
 *
 * @since 0.1
 * @package Radium
 * @subpackage Output
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class View
{
    private static $obLevel;
    public static $theme;
    public static $inheritFrom;
    private static $searchPaths = array();
    private static $vars = array();
    private static $viewExtensions = array('phtml', 'php', 'js.php', 'json.php');

    /**
     * Sends the variable to the view.
     *
     * @param string $var The variable name.
     * @param mixed $val The variables value.
     */
    public static function set($var, $val = null)
    {
        // Mass set
        if (is_array($var)) {
            foreach ($var as $k => $v) {
                static::set($k, $v);
            }
        } else {
            self::$vars[$var] = $val;
        }
    }

    /**
     * Renders the specified file.
     *
     * @param string $file
     * @param array $vars Variables to be passed to the view.
     */
    public static function render($file, array $vars = array())
    {
        return static::getView($file, $vars);
    }

    /**
     * Private function to handle the rendering of files.
     *
     * @param string $file
     * @param array  $vars Variables to be passed to the view.
     *
     * @return string
     */
    private static function getView($file, array $vars = array())
    {
        // Get the file name/path
        $path = self::filePath($file);

        if (!$path) {
            $file = str_replace('Controllers', 'Views', $file);
            Error::halt("View Error", "Unable to load view '{$file}'");
        }

        // Make the set variables accessible
        foreach (self::$vars as $_var => $_val) {
            $$_var = $_val;
        }

        // Make the vars for this view accessible
        foreach($vars as $_var => $_val) {
            $$_var = $_val;
        }

        // Shortcut for escaping HTML
        $e = function($string) {
            return htmlspecialchars($string);
        };

        // Shortcut for Language::translate()
        $t = function($string, Array $vars = array()) {
            return Language::translate($string, $vars);
        };

        // Load up the view and get the contents
        ob_start();
        include($path);
        return ob_get_clean();
    }

    /**
     * Determines the path of the view file.
     *
     * @param string $file File name.
     *
     * @return string
     */
    private static function filePath($file)
    {
        // Remove the App\Controllers namespace from the file path
        $file = explode('\\', $file);
        if (isset($file[1]) and $file[1] == 'Controllers') {
            $namespace = "{$file[0]}/{$file[1]}";
            unset($file[0], $file[1]);
        }

        $file = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_' . '\\1', implode('/', $file)));

        // Get the path
        $path = static::find((isset($namespace) ? "{$namespace}/" : '') . "{$file}");

        unset($file);
        return $path;
    }

    /**
     * Attempts to locate the view path in
     * all registered search paths.
     *
     * @param string $file
     *
     * @return mixed
     */
    public static function find($file)
    {
        $searchFor = array();
        $ofile = $file;

        // If the path includes a vendor name
        // add the path for it.
        $vendor = explode('/', $file);
        $vendor = $vendor[0];
        if ($path = Loader::pathForNamespace($vendor)) {
            // With theme
            if (static::$theme !== null) {
                $searchFor[] = str_replace("{$vendor}/Controllers/", 'views/' . static::$theme, "{$path}/{$file}");
            }
            $searchFor[] = str_replace("{$vendor}/Controllers/", 'views/', "{$path}/{$file}");
        }

        // Add the theme directory if one is set
        if (static::$theme !== null) {
            $searchFor[] = Loader::defaultNamespacePath() . '/views/' . static::$theme . "/{$file}";
        }

        // Add the inheritance path, if set
        if (static::$inheritFrom !== null) {
            $searchFor[] = static::$inheritFrom . "/{$file}";
        }

        // And the root of the views path
        $searchFor[] = Loader::defaultNamespacePath() . "/views/{$file}";

        foreach ($searchFor as $path) {
            foreach (static::$viewExtensions as $ext) {
                if (file_exists("{$path}.{$ext}")) {
                    return "{$path}.{$ext}";
                }
            }
        }

        // Not found
        return false;
    }

    /**
     * Converts a controller/method namespace to it's view path.
     *
     * @param string $namespace
     *
     * @return string
     */
    public static function pathForNamespace($namespace)
    {
        // Convert the namespace and method to a directory structure
        $namespace = str_replace(array('\\', '::'), '/', $namespace);

        // Change the controllers segment to views
        $namespace = str_replace('controllers', 'views', $namespace);

        return $namespace;
    }
}
