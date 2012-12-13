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

use Radium\Exception;

/**
 * Radium's Database class.
 *
 * @since 0.1
 * @package Radium
 * @subpackage Database
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Database
{
    protected static $connections = [];

    /**
     * Creates a new database connection.
     *
     * @param string $name   Connection name
     * @param array  $config Connection configuration
     *
     * @return object
     */
    public static function factory($name = 'default', array $config)
    {
        // Make sure a connection with same name doesn't exist
        if (array_key_exists($name, static::$connections)) {
            throw new Exception("Database connection {$name} already exists.");
        }

        // Set the class name, with the namespace
        $className = '\Radium\Database\\'. $config['driver'];

        // Connect to the database and return the object
        static::$connections[$name] = new $className($config, $name);
        return static::$connections[$name];
    }

    /**
     * Returns the database object for the specified connection name.
     *
     * @return object
     */
    public static function connection($name = 'default')
    {
        return array_key_exists($name, static::$connections) ? static::$connections[$name] : false;
    }
}
