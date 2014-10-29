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

namespace Radium\Database;

use Exception;

/**
 * Radium's database connection manager.
 *
 * @since 0.1
 * @author Jack Polgar <jack@polgar.id.au>
 */
class ConnectionManager
{
    protected static $connections = array();

    /**
     * Creates a new database connection.
     *
     * @param array  $config Connection configuration
     * @param string $name   Connection name
     *
     * @return object
     */
    public static function create(array $config, $name = 'default')
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
