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
use Radium\Database\PDO\Query;
use Radium\Database\PDO\Statement;

/**
 * PDO Database driver
 *
 * @since 0.2
 * @package Radium
 * @subpackage Database
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class PDO extends Driver
{
    private $connection;
    private $connectionName;
    private $queryCount = 0;
    protected $lastQuery;

    public $prefix;

    /**
     * PDO wrapper constructor.
     *
     * @param array $config Database config array
     */
    public function __construct($config, $name)
    {
        // Lowercase the database type
        $config['type'] = strtolower($config['type']);

        // Set connection name and table prefix
        $this->connectionName = $name;
        $this->prefix = isset($config['prefix']) ? $config['prefix'] : '';

        // Check if a DSN is already specified
        if (isset($config['dsn'])) {
            $dsn = $config['dsn'];
        }
        // SQLite DSN
        elseif ($config['type'] == 'sqlite') {
            $dsn = strtolower("sqlite:" . $config['path']);
        }
        // Something else... such as MySQL
        else {
            $dsn = strtolower($config['type']) . ':dbname=' . $config['database'] . ';host=' . $config['host'];
        }

        // Connect to the database
        $this->connection = new \PDO(
            $dsn,
            isset($config['username']) ? $config['username'] : null,
            isset($config['password']) ? $config['password'] : null,
            isset($config['options']) ? $config['options'] : array()
        );

        unset($dsn);
    }

    /**
     * Quotes a string for use in a query.
     *
     * @param string  $string String to quote
     * @param integer $type   Paramater type
     */
    public function quote($string, $type = \PDO::PARAM_STR)
    {
        return $this->connection->quote($string, $type);
    }

    /**
     * Executes an SQL statement, returning a result set as a Statement object.
     *
     * @param string $query
     *
     * @return mixed
     */
    public function query($query)
    {
        // Replace prefix placeholder with prefix
        $query = str_replace("{prefix}", $this->prefix, $query);

        // Log last query and query count
        $this->lastQuery = $query;
        $this->queryCount++;

        // Query database
        return $this->connection->query($query);
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param string $query   SQL Query
     * @param array  $options Driver options (not used)
     *
     * @return object
     */
    public function prepare($query, array $options = array())
    {
        // Replace prefix placeholder with prefix
        $query = str_replace("{prefix}", $this->prefix, $query);

        // Log last query and query count
        $this->lastQuery = $query;
        $this->queryCount++;

        // Statement time
        return new Statement($this->connection->prepare($query, $options), $this->connectionName);
    }

    /**
     * Returns a select query builder object.
     *
     * @param array $cols Columns to select
     *
     * @return object
     */
    public function select($cols = array('*'))
    {
        if (!is_array($cols)) {
            $cols = func_get_args();
        }
        return new Query("SELECT", $cols, $this->connectionName);
    }

    /**
     * Returns an update query builder object.
     *
     * @param string $table Table name
     *
     * @return object
     */
    public function update($table)
    {
        return new Query("UPDATE", $table, $this->connectionName);
    }

    /**
     * Returns a delete query builder object.
     *
     * @return object
     */
    public function delete()
    {
        return new Query("DELETE", null, $this->connectionName);
    }

    /**
     * Returns an insert query builder object.
     *
     * @param array $data Data to insert
     *
     * @return object
     */
    public function insert(array $data)
    {
        return new Query("INSERT INTO", $data, $this->connectionName);
    }

    /**
     * Checks if the table exists.
     *
     * @param string $table
     *
     * @return boolean
     */
    public function tableExists($table)
    {
        try {
            $result = $this->query("SELECT 1 FROM `{$table}` LIMIT 1");
        } catch (\Exception $e) {
            return false;
        }

        return $result !== false;
    }

    /**
     * Returns the ID of the last inserted row.
     *
     * @return integer
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Retrieve a database connection attribute.
     *
     * @param integer $attribute
     *
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return $this->connection->getAttribute($attribute);
    }

    /**
     * Returns the number of queries that
     * have been executed.
     *
     * @return integer
     */
    public function queryCount()
    {
        return $this->queryCount;
    }
}
