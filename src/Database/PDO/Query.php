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
    protected $connectionName;

    /**
     * The model to put the data into.
     *
     * @var string
     */
    protected $model;

    /**
     * The query, joined together when needed.
     *
     * @var array
     */
    protected $query = array();

    /**
     * Table prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * Values that need to be binded to the query.
     *
     * @var array
     */
    protected $valuesToBind = array();

    /**
     * Whether or not to merge the next `where()` called with the previous.
     *
     * @var boolean
     */
    protected $mergeNextWhere = false;

    /**
     * PDO Query builder constructor.
     *
     * @param string $type
     * @param mixed  $data
     * @param string $connectionName
     *
     * @return object
     */
    public function __construct($type, $data = null, $connectionName = 'default')
    {
        // Set connection name
        $this->connectionName = $connectionName;

        $this->query['type'] = $type;

        // Set the prefix
        $this->prefix = $this->connection()->prefix;

        // Figure out what to do with the
        // $data parameter.
        switch ($type) {
            case "SELECT":
            case "SELECT DISTINCT":
                $this->query['select'] = ($data) ? $data : array('*');
                break;

            case "INSERT INTO":
                $this->query['data'] = $data;
                break;

            case "UPDATE":
                $this->tableName($data);
                break;
        }
    }

    /**
     * Enable use of the model object for table rows.
     *
     * @param string $model The model class.
     *
     * @return object
     */
    public function model($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Sets the table name.
     *
     * @param string $name
     */
    private function tableName($name = null)
    {
        if ($name) {
            $this->query['table'] = $name;
        }

        return $this->query['table'];
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
        $this->tableName($table);
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
        $this->tableName($table);
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
            $this->query['order_by'] = array();
        }

        // Multiple?
        if (is_array($column)) {
            foreach ($column as $col => $how) {
                $this->orderBy($col, $how);
            }
        } else {
            $this->query['order_by'][] = $this->columnName($column) . " {$how}";
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
     * Tells the query builder whether to merge the next `where()`
     * call with the previous one or not.
     *
     * @param integer $group
     *
     * @return object
     */
    public function mergeNextWhere($group = true)
    {
        $this->mergeNextWhere = $group;
        return $this;
    }

    /**
     * Easily add a "table = something" to the query.
     *
     * @example
     *    where("count = ?", 5)
     *    // or
     *    where(["count = ?" => 5], ["name LIKE 'Radium%'"]);
     *
     * @param string $columm Column name
     * @param mixed  $value  Column value
     *
     * @return object
     */
    public function where($column, $value = null)
    {
        if (!isset($this->query['where'])) {
            $this->query['where'] = array();
        }

        // Are we merging this with the previous?
        // This is used when coming out of Model.
        if ($this->mergeNextWhere and count($this->query['where']) > 0) {
            $this->mergeNextWhere = false;
            return $this->_and($column, $value);
        }

        // Array? too easy
        if (is_array($column)) {
            $this->query['where'][] = $column;
        } else {
            $this->query['where'][] = array($column => $value);
        }

        return $this;
    }

    /**
     * Adds a filter to the last condition group.
     *
     * @param string $columm Column name
     * @param mixed  $value  Column value
     *
     * @return object
     */
    public function _and($column, $value = null) {
        if (!is_array($column)) {
            $column = array($column => $value);
        }

        $this->query['where'][count($this->query['where']) - 1] = array_merge(
            $this->query['where'][count($this->query['where']) - 1],
            $column
        );

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
     * Allows us to trick PHP into letting us use the `and` and `or` keywords as
     * functions.
     *
     * @param string $method
     * @param mixed  $args
     */
    public function __call($method, $args = null)
    {
        if ($method === 'and' or $method === 'or') {
            return call_user_func_array(array($this, "_{$method}"), $args);
        }

        // I seem to have fallen and can't get up.
        throw new \BadMethodCallException;
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
     * Join another table.
     *
     * @param string $table
     * @param string $on
     * @param array  $columns
     *
     * @return object
     */
    public function join($table, $on, array $columns = array())
    {
        if (!array_key_exists('joins', $this->query)) {
            $this->query['joins'] = array();
        }

        $this->query['select'] = array_merge($this->query['select'], $columns);
        $this->query['joins'][] = array($table, $on);

        return $this;
    }

    /**
     * Fetch first row.
     *
     * @return array
     */
    public function fetch()
    {
        return $this->exec()->fetch();
    }

    /**
     * Fetch all rows.
     *
     * @return array
     */
    public function fetchAll()
    {
        return $this->exec()->fetchAll();
    }

    /**
     * Returns the row count.
     *
     * @return integer
     */
    public function rowCount()
    {
        return $this->exec()->rowCount();
    }

    /**
     * Executes the query and return the statement.
     *
     * @return object
     */
    public function exec()
    {
        $result = $this->connection()->prepare($this->assemble());

        foreach ($this->valuesToBind as $key => $value) {
            $result->bindValue(":{$key}", $value);
        }

        return $result->model($this->model)->exec();
    }

    /**
     * Private method used to compile the query into a string.
     *
     * @return string
     */
    public function assemble()
    {
        $queryString = array();

        $queryString[] = $this->query['type'];

        // Select query
        if ($this->query['type'] == "SELECT"
        or  $this->query['type'] == "SELECT DISTINCT") {
            // Build columns to select
            $queryString[] = $this->buildSelectColumns();

            // From
            $queryString[] = "FROM `{$this->query['table']}`";

            // Joins
            if (array_key_exists('joins', $this->query)) {
                $queryString[] = $this->buildJoins();
            }

            // Where
            $queryString[] = $this->buildWhere();

            // Custom SQL
            if (array_key_exists('sql', $this->query)) {
                $queryString[] = $this->query['sql'];
            }

            // Order by
            if (array_key_exists('order_by', $this->query)) {
                $queryString[] = "ORDER BY " . implode(", ", $this->query['order_by']);
            }
        }
        // Insert
        elseif ($this->query['type'] == "INSERT INTO") {
            // Table
            $queryString[] = "`{$this->query['table']}`";

            // Get the columns and values
            $columns = $values = array();
            foreach ($this->query['data'] as $column => $value) {
                $columns[] = $this->columnName($column);
                $values[] = $this->processValue($value);
            }

            // Add columns and values to query
            $queryString[] = "(" . implode(',', $columns) . ")";
            $queryString[] = "VALUES (" . implode(',', $values) . ")";
        }
        // Update
        elseif ($this->query['type'] == "UPDATE") {
            // Table
            $queryString[] = "`{$this->query['table']}`";

            // Set values
            $values = array();
            foreach ($this->query['data'] as $column => $value) {
                // Process column name
                $column = $this->columnName($column);

                // Add value to bind queue
                $valueBindKey = "new_" . str_replace(array('.', '`'), array('_', ''), $column);
                $this->valuesToBind[$valueBindKey] = $value;

                // Add to values
                $values[] = $column . " = :{$valueBindKey}";
            }

            // Add values to query
            $queryString[] = "SET " . implode(", ", $values);

            $queryString[] = $this->buildWhere();
        }
        // Delete from
        elseif ($this->query['type'] == "DELETE") {
            // Table
            $queryString[] = "FROM `{$this->query['table']}`";

            // Where
            $queryString[] = $this->buildWhere();
        }

        return implode(" ", str_replace("{prefix}", $this->prefix, $queryString));
    }

    /**
     * Builds the columns for the select queries.
     *
     * @return string
     */
    private function buildSelectColumns()
    {
        $columns = array();

        foreach ($this->query['select'] as $column => $as) {
            // Normal column select
            if (is_numeric($column)) {
                $columns[] = $this->columnName($as);
            }
            // Alias
            else {
                $columns[] = $this->columnName($column) . " AS `{$as}`";
            }
        }

        // Join columns and return
        return implode(', ', $columns);
    }

    /**
     * Makes the column name safe for queries.
     *
     * @return string
     */
    private function columnName($column)
    {
        // Select all
        if ($column == '*') {
            return "`{$this->query['table']}`.*";
        }

        if (strpos($column, '.') === false) {
            $column = $this->query['table'] . ".{$column}";
        }

        // Regular column name
        if (strpos($column, '(') === false) {
            return str_replace(array('.'), array('`.`'), "`{$column}`");
        } else {
            return trim(str_replace(array('(', ')', '.'), array('(`', '`)', '`.`'), $column), '`');
        }
    }

    /**
     * Compiles the where conditions.
     *
     * @return string
     */
    private function buildWhere()
    {
        $query = array();

        // Make sure there's something to do
        if (isset($this->query['where']) and count($this->query['where'])) {
            foreach ($this->query['where'] as $group => $conditions) {
                $group = array(); // Yes, because the $group above is not used, get over it.
                foreach ($conditions as $condition => $value) {
                    // Get column name
                    $cond = explode(" ", $condition);
                    $column = str_replace('`', '', $cond[0]);
                    $safeColumn = $this->columnName($column);

                    // Make the column name safe
                    $condition = str_replace($column, $safeColumn, $condition);

                    // Add value to the bind queue
                    $valueBindKey = str_replace(array('.', '`'), array('_', ''), $safeColumn);

                    if (!empty($value) or $value !== null) {
                        $this->valuesToBind[$valueBindKey] = $value;
                    }

                    // Add condition to group
                    $group[] = str_replace("?", ":{$valueBindKey}", $condition);
                }

                // Add the group
                $query[] = "(" . implode(" AND ", $group) . ")";
            }

            // Return
            return "WHERE " . implode(" OR ", $query);
        }
    }

    /**
     * Compiles the joins.
     *
     * @return string
     */
    private function buildJoins()
    {
        $joins = array();

        foreach ($this->query['joins'] as $join) {
            // Handle join with alias
            if (is_array($join[0])) {
                $joins[] = "LEFT JOIN `{$join[0][0]}` `{$join[0][1]}` on {$join[1]}";
            }
            // Handle regular joining
            else {
                $joins[] = "LEFT JOIN `{$join[0]}` on {$join[1]}";
            }
        }

        return implode(" ", $joins);
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
        } elseif (is_array($value)) {
            return $this->connection()->quote(json_encode($value));
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
