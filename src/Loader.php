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
    private static $vendorDirectory;
    private static $registeredPaths = array();
    private static $registeredNamespaces = array();

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
     * Returns the vendor directory path.
     *
     * @return string
     */
    public static function vendorDirectory()
    {
        return static::$vendorDirectory;
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
    public static function registerNamespace($vendor, $path)
    {
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
