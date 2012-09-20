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

namespace Radium\Database\PDO;

use Radium\Database;
use Radium\Database\PDO;

/**
 * PDO Database wrapper query builder
 *
 * @package Radium
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class Query
{
    /**
     * The name of the connection to use.
     *
     * @var string
     */
    private $connectionName;

    /**
     * The model to put the data into.
     *
     * @var string
     */
    private $model;

    /**
     * The query, joined together when needed.
     *
     * @var array
     */
    private $query = [];

    /**
     * Table prefix
     *
     * @var string
     */
    private $prefix;

    /**
     * Values that need to be binded to the query.
     *
     * @var array
     */
    private $valuesToBind = [];

    /**
     * PDO Query builder constructor.
     *
     * @param string $type
     * @param mixed  $data
     * @param string $connectionName
     *
     * @return object
     */
    public function __construct($type, $data = null, $connectionName = 'main')
    {
        // Set connection name
        $this->connectionName = $connectionName;

        $this->query['type'] = $type;

        // Figure out what to do with the
        // $data parameter.
        switch ($type) {
            case "SELECT":
            case "SELECT DISTINCT":
                $this->query['select'] = ($data) ? $data : '*';
                break;

            case "INSERT INTO":
            case "UPDATE":
                $this->query['table'] = $data;
                break;
        }

        // Set the prefix
        //$this->prefix = $this->connection()->prefix;

        return $this;
    }

    /**
     * Enable use of the model object for table rows.
     *
     * @param string $model The model class.
     *
     * @return object
     */
    public function _model($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     * Set the table to select/delete from.
     *
     * @param string $table
     *
     * @return object
     */
    public function from($table)
    {
        $this->query['table'] = $table;
        return $this;
    }

    /**
     * Set the table to insert data into.
     *
     * @param string $table
     *
     * @return object
     */
    public function into($table)
    {
        $this->query['table'] = $table;
        return $this;
    }

    /**
     * Sets the column => value data.
     *
     * @param array $data
     *
     * @return object
     */
    public function set(array $data)
    {
        $this->query['data'] = $data;
        return $this;
    }

    /**
     * Orders the query rows.
     *
     * @param string $column Column
     * @param string $how    Direction
     *
     * @return object
     */
    public function orderBy($column, $how = 'ASC')
    {
        if (!array_key_exists('order_by', $this->query)) {
            $this->query['order_by'] = [];
        }

        // Multiple?
        if (is_array($column)) {
            foreach ($column as $col => $how) {
                $this->orderBy($col, $how);
            }
        } else {
            $this->query['order_by'][] = str_replace('.', "`.`", "`{$column}` {$how}");
        }
        return $this;
    }

    /**
     * Insert custom SQL into the query.
     *
     * @param string $sql
     *
     * @return object
     */
    public function sql($sql)
    {
        $this->query['sql'] = $sql;
        return $this;
    }

    /**
     * Easily add a "table = something" to the query.
     *
     * @example
     *    where("count = ?", 5)
     *    // or
     *    where([["count = ?", 5], ["name LIKE 'Radium%'"]]);
     *
     * @param string $columm Column name
     * @param mixed  $value  Column value
     *
     * @return object
     */
    public function where($column, $value = null)
    {
        if (!array_key_exists('where', $this->query)) {
            $this->query['where'] = [];
        }

        // Array? too easy
        if (is_array($column)) {
            $this->query['where'][] = $column;
        } else {
            $this->query['where'][] = [[$column, $value]];
        }

        return $this;
    }

    /**
     * An alias for where()
     *
     * @see Radium\Database\PDO\Query::where()
     */
    public function _or($column, $value = null)
    {
        $this->where($column, $value);
        return $this;
    }

    /**
     * Limits the query rows.
     *
     * @param integer $from
     * @param integer $to
     *
     * @return object
     */
    public function limit($from, $to = null)
    {
        $this->query['limit'] = implode(',', func_get_args());
        return $this;
    }

    /**
     * Executes the query and return the statement.
     *
     * @return object
     */
    public function exec()
    {
        $result = $this->connection()->prepare($this->_assemble());

        return $result->_model($this->_model)->exec();
    }

    /**
     * Private method used to compile the query into a string.
     *
     * @return string
     */
    public function assemble()
    {
        $queryString = [];

        $queryString[] = $this->query['type'];

        // Select query
        if ($this->query['type'] == "SELECT"
        or  $this->query['type'] == "SELECT DISTINCT") {
            // Build columns to select
            $columns = [];
            foreach ($this->query['select'] as $column => $as) {
                // Normal column select
                if (is_numeric($column)) {
                    $columns[] = "`{$as}`";
                }
                // Alias
                else {
                    $columns[] = "`{$column}` AS `{$as}`";
                }
            }

            // Join columns
            $queryString[] = implode(', ', $columns);
            unset($columns);

            // From
            $queryString[] = "FROM `{$this->prefix}{$this->query['table']}`";

            // Where
            $queryString[] = $this->buildWhere();

            // Order by
            $queryString[] = "ORDER BY " . implode(", ", $this->query['order_by']);
        }

        return implode(" ", str_replace("%prefix%", $this->prefix, $queryString));
    }

    private function buildWhere()
    {
        $query = [];

        // Make sure there's something to do
        if (count($this->query['where'])) {
            foreach ($this->query['where'] as $group => $conditions) {
                $group = []; // Yes, because the $group above is not used, get over it.
                foreach ($conditions as $condition) {
                    // Get column name
                    $column = str_replace('`', '', explode(" ", $condition[0])[0]); // PHP 5.4 array dereferencing, fuck yeah!

                    $condition[0] = str_replace([$column, '.'], ["`{$column}`", "`.`"], $condition[0]);

                    // Add value to the bind queue
                    $this->valuesToBind[] = $condition[1];
                    $valueBindKey = count($this->valuesToBind) - 1;

                    // Add condition to group
                    $group[] = str_replace("?", ":{$valueBindKey}_{$column}", $condition[0]);
                }

                // Add the group
                $query[] = "(" . implode(" AND ", $group) . ")";
            }

            // Return
            return "WHERE " . implode(" OR ", $query);
        }
    }

    /**
     * Processes the value.
     *
     * @return mixed
     */
    private function processValue($value)
    {
        if ($value === "NOW()") {
            return $this->connection()->quote(gmdate("Y-m-d H:i:s"));
        } elseif ($value === "NULL") {
            return 'NULL';
        } else {
            return $this->connection()->quote($value);
        }
    }

    /**
     * Private function to return the database connection.
     *
     * @return object
     */
    private function connection()
    {
        return Database::connection($this->connectionName);
    }

    /**
     * Magic method that converts the query to a string.
     * And some PHP team members have said PHP is not a magic language,
     * this is why Ruby is better.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->assemble();
    }
}
