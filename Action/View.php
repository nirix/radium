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

namespace Radium\Action;

use Radium\Loader;
use Radium\Error;
use Radium\Language;

/**
 * Radium's View rendering class.
 *
 * @since 0.1
 * @package Radium/Action
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class View
{
    protected static $searchPaths = array();
    protected static $vars        = array();
    protected static $extensions  = array(
        'phtml', 'php'
    );

    /**
     * Sends the variable to the view.
     *
     * @param string $var The variable name.
     * @param mixed $val The variables value.
     */
    public static function set($name, $value)
    {
        // Mass set
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                static::set($k, $v);
            }
        } else {
            self::$vars[$name] = $value;
        }
    }

    /**
     * Renders the specified view.
     *
     * @param string $file
     * @param array  $variables Variables to be passed to the view.
     *
     * @return string
     */
    public static function render($view, array $variables = array())
    {
        $filePath = static::filePath($view);

        if (!$filePath) {
            $error = array(
                "Unable to load view for '{$view}'<br>",
                "Search paths:<br>",
                "<pre>"
            );

            foreach (static::$searchPaths as $path) {
                $error[] = $path;
            }

            $error[] = "</pre>";

            Error::halt("View Error", implode(PHP_EOL, $error));
        }

        // Global view variables
        foreach (static::$vars as $_varName => $_varValue) {
            $$_varName = $_varValue;
        }
        unset($_varName, $_varValue);

        // Local view variables
        foreach ($variables as $_varName => $_varValue) {
            $$_varName = $_varValue;
        }
        unset($_varName, $_varValue);

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
        include($filePath);
        return ob_get_clean();
    }

    /**
     * Convert to filename format and strip `Vendor\Controllers`.
     *
     * @param string $view
     *
     * @return string
     */
    public static function fileName($view)
    {
        return preg_replace("/^[\w\d]+\/Controllers\/([\w\d\/]+)/", "$1", $view);
    }

    /**
     * Searches for the view in the registered search paths.
     *
     * @param string $view View to render.
     *
     * @return string
     */
    public static function filePath($view)
    {
        $searchPaths   = static::$searchPaths;
        $searchPaths[] = str_replace("Controllers", "Views", Loader::vendorDirectory() . "/{$view}");

        // Strip `VendorName\Controllers` from the view
        $view = static::fileName($view);

        // Loop over search paths
        foreach ($searchPaths as $path) {
            foreach (static::$extensions as $ext) {
                if ($filePath = "{$path}.{$ext}" and file_exists($filePath)) {
                    return $filePath;
                } elseif ($filePath = "{$path}/{$view}.{$ext}" and file_exists($filePath)) {
                    return $filePath;
                }
            }
        }

        return false;
    }

    /**
     * Adds a path to search for views in.
     *
     * @param string  $path    Path to search.
     * @param boolean $prepend Add path to the top of the list.
     */
    public static function addSearchPath($path, $prepend = false)
    {
        if ($prepend) {
            static::$searchPaths = array_merge(
                array($path),
                static::$searchPaths
            );
        } else {
            static::$searchPaths[] = $path;
        }
    }
}
