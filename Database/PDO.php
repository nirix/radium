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

namespace Radium\Database;

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
        try {
            $this->connection = new \PDO(
                $dsn,
                isset($config['username']) ? $config['username'] : null,
                isset($config['password']) ? $config['password'] : null,
                isset($config['options']) ? $config['options'] : array()
            );

            unset($dsn);
        }
        // Unable to connect, display error
        catch (\PDOException $e) {
            $this->halt($e->getMessage());
        }
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
        $this->queryCount++;
        $this->lastQuery = $query;

        $rows = $this->connection->query($query);
        return $rows;
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
        $this->lastQuery = $query;
        $this->queryCount++;
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
     * Returns the ID of the last inserted row.
     *
     * @return integer
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
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
