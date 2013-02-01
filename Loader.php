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

namespace Radium;

/**
 * Loader class.
 *
 * @since 0.1
 * @package Radium
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Loader
{
    private static $defaultNamespace;
    private static $vendorDirectory;
    private static $registeredPaths = array();
    private static $registeredNamespaces = array();

    /**
     * Returns the default namespace name.
     *
     * @return string
     */
    public static function defaultNamespace()
    {
        return static::$defaultNamespace['name'];
    }

    /**
     * Returns the default namespace path.
     *
     * @return string
     */
    public static function defaultNamespacePath()
    {
        return static::$defaultNamespace['path'];
    }

    /**
     * Sets the vendor directory location.
     *
     * @param string $path
     */
    public static function setVendorDirectory($path)
    {
        static::$registeredPaths[] = $path;
        static::$vendorDirectory = $path;
    }

    /**
     * Registers a path to search in.
     *
     * @param string $path
     */
    public static function registerPath($path)
    {
        static::$registeredPaths[] = $path;
    }

    /**
     * Registers a specific vendors path.
     *
     * @param string $vendor
     * @param string $path
     */
    public static function registerNamespace($vendor, $path, $default = false)
    {
        if ($default) {
            static::$defaultNamespace = array('name' => $vendor, 'path' => $path);
        }
        static::$registeredNamespaces[$vendor] = $path;
    }

    /**
     * Checks if the specified vendor is registered.
     *
     * @param string $vendor
     *
     * @return mixed
     */
    public static function registeredNamespace($vendor)
    {
        return (array_key_exists($vendor, static::$registeredNamespaces) ? static::$registeredNamespaces[$vendor] : false);
    }

    /**
     * Returns the path for the specified vendor.
     *
     * @param string $namespace
     *
     * @return string
     */
    public static function pathForNamespace($namespace)
    {
        if ($path = static::registeredNamespace($namespace)) {
            return $path;
        } elseif ($path = static::$vendorDirectory . "/{$namespace}" and is_dir($path)) {
            return $path;
        }

        return false;
    }

    /**
     * Searches for the specified class.
     *
     * @param string $class
     * @param string $vendor Only look in this vendors directory.
     *
     * @return mixed
     */
    public static function find($class, $vendor = null)
    {
        // Remove the vendor from the beginning of the class.
        if (strpos(trim($class, "\\"), $vendor) === 0) {
            $class = explode("\\", $class);
            unset($class[0]);
            $class = implode("\\", $class);
        }

        // Convert backslashes to forward slashes.
        $file = str_replace("\\", "/", $class) . ".php";

        // Check supplied vendor directory
        if ($vendor !== null) {
            // If path is registered, use that path.
            if ($vendorPath = static::registeredNamespace($vendor)) {
                $path = $vendorPath . "/{$file}";
            }
            // Use vendor directory
            else {
                $path = static::$vendorDirectory . "/{$vendor}/{$file}";
            }

            if (file_exists($path)) {
                return $path;
            }
        }
        // Search for the file.
        else {
            foreach (static::$registeredPaths as $dir) {
                $path = "{$dir}/{$file}";
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        // Not found
        return false;
    }
}
