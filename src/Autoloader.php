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

require __DIR__ . '/Loader.php';

/**
 * Radium's Autoloader, the magic behind the scenes.
 *
 * @since 0.1
 * @package Radium
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Autoloader
{
    private static $classes = array();

    /**
     * Registers the class as the autoloader.
     */
    public static function register()
    {
        spl_autoload_register('Radium\Autoloader::load', true, true);
    }

    /**
     * Register multiple namespaces at once.
     *
     * @param array $namespaces
     */
    public static function registerNamespaces(array $namespaces)
    {
        foreach ($namespaces as $vendor => $location) {
            static::registerNamespace($vendor, $location);
        }
    }

    /**
     * Registers a namespace location.
     *
     * @param string $vendor
     * @param string $location
     */
    public static function registerNamespace($vendor, $location)
    {
        Loader::registerNamespace($vendor, $location);
    }

    /**
     * Alias multiple classes at once.
     *
     * @param array $classes
     */
    public static function aliasClasses($classes)
    {
        foreach ($classes as $original => $alias) {
            static::aliasClass($original, $alias);
        }
    }

    /**
     * Alias a class from a complete namespace to just it's name.
     *
     * @param string $original
     * @param string $alias
     */
    public static function aliasClass($original, $alias)
    {
        static::$classes[$alias] = ltrim($original, '\\');
    }

    /**
     * Loads a class
     *
     * @param string $class The class
     *
     * @return bool
     */
    public static function load($class)
    {
        $class = ltrim($class, '\\');
        $namespace = explode('\\', $class);
        $vendor = $namespace[0];

        // Aliased classes
        if (array_key_exists($class, static::$classes)) {
            // Make sure the class doesn't exist
            // before trying to load it.
            if (!class_exists(static::$classes[$class])) {
                static::load(static::$classes[$class]);
            }

            // If the class exists, alias it if it won't conflict with anything.
            if (!class_exists($class) and class_exists(static::$classes[$class])) {
                class_alias(static::$classes[$class], $class);
            }
        }
        // Registered namespace
        elseif ($path = Loader::registeredNamespace($vendor)) {
            $path = Loader::find($class, $vendor);
            if (file_exists($path)) {
                require $path;
            }
        }
        // Everything else
        else {
            $file = Loader::find($class);
            if (file_exists($file)) {
                require $file;
            }
        }
    }
}
