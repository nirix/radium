<?php
/*!
 * Radium
 * Copyright (C) 2011-2014 Jack P.
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
            Error::halt("View Error", "Unable to load view '{$view}'");
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

        ob_start();
        include($filePath);
        return ob_get_clean();
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
        $view = preg_replace("/^[\w\d]+\/Controllers\/([\w\d\/]+)/", "$1", $view);

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
