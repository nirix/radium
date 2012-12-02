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

require __DIR__ . '/Exception.php';

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
    private static $vendorLocation;
    private static $classes = array();

    /**
     * Registers the class as the autoloader.
     */
    public static function register()
    {
        spl_autoload_register('Radium\Autoloader::load', true, true);
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
     * Sets the vendor location.
     *
     * @param string $location
     */
    public static function vendorLocation($location)
    {
        static::$vendorLocation = $location;
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

        // Aliased classes
        if (array_key_exists($class, static::$classes)) {
            $file = static::filePath(static::$classes[$class]);

            if (file_exists($file) and !class_exists(static::$classes[$class])) {
                require $file;
            }

            if (class_exists(static::$classes[$class])) {
                class_alias(static::$classes[$class], $class);
            }
        }
        // Everything else
        else {
            $file = static::filePath($class);
            if (file_exists($file)) {
                require $file;
            }
        }
    }

    /**
     * Converts the class into the file path.
     *
     * @param string $class
     *
     * @return string
     */
    public static function filePath($class)
    {
        return static::$vendorLocation . str_replace(['\\', '_'], DIRECTORY_SEPARATOR, "/{$class}.php");
    }
}
