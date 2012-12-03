<?php
/*!
 * Radium
 * Copyright (C) 2011-2012 Jack P.
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

use Radium\Error;

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
    private static $searchPaths = [];
    private static $vars = [];
    private static $viewExtensions = ['phtml', 'php', 'js.php', 'json.php'];

    /**
     * Renders the specified file.
     *
     * @param string $file
     * @param array $vars Variables to be passed to the view.
     */
    public static function render($file, array $vars = [])
    {
        // Get the view content
        $content = static::get($file, $vars);

        // Check if we need to flush or append
        if(ob_get_level() > static::$obLevel + 1) {
            ob_end_flush();
        }
        // Append it to the output
        else {
            Body::append($content);
            @ob_end_clean();
        }
    }

    /**
     * Private function to handle the rendering of files.
     *
     * @param string $file
     * @param array $vars Variables to be passed to the view.
     *
     * @return string
     */
    public static function get($file, array $vars = [])
    {
        // Get the file name/path
        $file = static::filePath($file);

        // Make sure the ob_level is set
        if (static::$obLevel === null) {
            static::$obLevel = ob_get_level();
        }

        // Make the variables available to the view
        foreach (array_merge(static::$vars, $vars) as $_var => $_val) {
            $$_var = $_val;
        }

        // Load up the view and get the contents
        ob_start();
        include($file);
        $content = ob_get_contents();

        return $content;
    }

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
     * Determines the path of the view file.
     *
     * @param string $file File name.
     *
     * @return string
     */
    private static function filePath($file)
    {
        // Remove the namespace from the file path
        $file = explode('\\', $file); // Stupid "pass by reference" shit
        $file = str_replace('\\', '/', strtolower(array_pop($file)));

        // Get the path
        $path = static::exists($file);

        // Check if the file was found
        if (!$path) {
            Error::halt("View Error", "Unable to load view '{$file}'");
        }

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
    public static function exists($file)
    {
        $dirs = [];

        // Add the theme directory if one is set
        if (static::$theme !== null) {
            $dirs[] = APPPATH . '/views/' . static::$theme;
        }

        // Add the registered search paths
        $dirs = array_merge($dirs, static::$searchPaths);

        // Add the inheritance path, if set
        if (static::$inheritFrom !== null) {
            $dirs[] = static::$inheritFrom;
        }

        // And the root of the views path
        $dirs[] = APPPATH . '/views';

        // Search time
        foreach ($dirs as $dir) {
            foreach (static::$viewExtensions as $ext) {
                $path = "{$dir}/{$file}.{$ext}";
                if (file_exists($path)) {
                    // Found!
                    return $path;
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
        $namespace = str_replace(['\\', '::'], '/', $namespace);

        // Change the controllers segment to views
        $namespace = str_replace('controllers', 'views', $namespace);

        return $namespace;
    }
}