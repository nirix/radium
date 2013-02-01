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
    private $query = array();

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
    private $valuesToBind = array();

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
     * Sets the table name with the prefix.
     *
     * @param string $name
     */
    private function tableName($name = null)
    {
        if ($name) {
            $this->query['table'] = "{$this->prefix}{$name}";
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
            $this->query['where'] = array();
        }

        // Array? too easy
        if (is_array($column)) {
            $this->query['where'][] = $column;
        } else {
            $this->query['where'][] = array(array($column, $value));
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
            $result->bindValue($key, $value);
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
                foreach ($conditions as $condition) {
                    // Get column name
                    $cond = explode(" ", $condition[0]);
                    $column = str_replace('`', '', $cond[0]);
                    $safeColumn = $this->columnName($column);

                    // Make the column name safe
                    $condition[0] = str_replace($column, $safeColumn, $condition[0]);

                    // Add value to the bind queue
                    $valueBindKey = str_replace(array('.', '`'), array('_', ''), $safeColumn);
                    $this->valuesToBind[$valueBindKey] = $condition[1];

                    // Add condition to group
                    $group[] = str_replace("?", ":{$valueBindKey}", $condition[0]);
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
            $joins[] = "JOIN `{$join[0]}` ON {$join[1]}";
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
