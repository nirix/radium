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

use Radium\Database;
use Radium\Database\Validations;
use Radium\Helpers\Time;
use Radium\Core\Hook;

/**
 * Database Model class
 *
 * @package Avalon
 * @subpackage Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $_table;

    /**
     * Primary key field, uses `id` by default.
     *
     * @var string
     */
    protected static $_primaryKey = 'id';

    /**
     * Belongs to relationships.
     *
     * @var array
     */
    protected static $_belongsTo = [];

    /**
     * Has many relationships.
     *
     * @var array
     */
    protected static $_hasMany = [];

    /**
     * Field validations.
     *
     * @example
     *  $_validates = [
     *      'username' => ['unique' => true, 'maxLength' => 20] // Unique and max 20 characters
     *  ];
     *
     * @var array
     */
    protected static $_validates = [];

    /**
     * Model errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Name of the connection name if not the default one.
     *
     * @var string
     */
    protected static $_connectionName = 'default';

    /**
     * Is new row?
     *
     * @var boolean
     */
    protected $_isNew;

    /**
     * Table schema.
     *
     * @var array
     */
    protected static $_schema =[];

    /**
     * Filters to process data before certain events.
     *
     * @var array
     */
    protected static $_before = [];

    /**
     * Filters to process data after certain events.
     *
     * @var array
     */
    protected static $_after  = [];

    /**
     * Model constructor.
     *
     * @param array $data
     * @param bool  $isNew
     */
    public function __construct(array $data = [], $isNew = true)
    {
        // Set defaults
        foreach (static::schema() as $field => $properties) {
            $this->{$field} = $properties['default'];
        }

        // Set data
        foreach ($data as $column => $value) {
            $this->{$column} = $value;
        }

        // Run filters
        $this->runFilters('after', 'construct');

        $this->_isNew = $isNew;
    }

    /**
     * Used to fetch the tables schema.
     *
     * @access protected
     */
    protected static function loadSchema()
    {
        // Make sure there's a place to store the schema
        if (!array_key_exists(static::$_table, static::$_schema)) {
            static::$_schema[static::$_table] = null;
        }

        // Make sure we haven't already fetched
        // the tables schema.
        if (static::$_schema[static::$_table] === null) {
            $result = static::connection()->prepare("DESCRIBE `" . static::$_table . "`")->exec();
            foreach ($result->fetchAll(\PDO::FETCH_COLUMN) as $column) {
                static::$_schema[static::$_table][$column['Field']] = [
                    'type'    => $column['Type'],
                    'default' => $column['Default'],
                    'null'    => $column['Null'] == 'NO' ? false : true,
                    'key'     => $column['Key'],
                    'extra'   => $column['Extra']
                ];
            }
        }
    }

    /**
     * Returns the Models schema.
     *
     * @return array
     */
    public static function schema()
    {
        static::loadSchema();
        return array_key_exists(static::$_table, static::$_schema) ? static::$_schema[static::$_table] : null;
    }

    /**
     * Returns the connection for the model.
     *
     * @return object
     * @access protected
     */
    protected static function connection()
    {
        return Database::connection(static::$_connectionName);
    }

    /**
     * Returns the first found row.
     *
     * @return object
     */
    public static function find($find, $value = null)
    {
        if ($value === null) {
            $value = $find;
            $find = static::$_primaryKey;
        }

        return static::select()->where($find. " = ?", $value)->fetch();
    }

    /**
     * Fetch all rows.
     *
     * @return array
     */
    public static function all()
    {
        return static::select()->fetchAll();
    }

    /**
     * Build a query based off the model.
     *
     * @return object
     */
    public static function select($fields = '*')
    {
        return static::connection()
            ->select()
            ->from(static::$_table)
            ->model(get_called_class());
    }

    /**
     * Runs the filters for the specified action.
     *
     * @param string $action
     */
    protected function runFilters($when, $action)
    {
        $when = "_{$when}";
        $filters = static::${$when};

        // Anything to do?
        if (array_key_exists($action, $filters)) {
            foreach ($filters[$action] as $method) {
                $this->{$method}();
            }
        }
    }

    /**
     * Updates the model attributes.
     *
     * @param array $attributes
     */
    public function set($attributes)
    {
        foreach ($attributes as $column => $value) {
            $this->{$column} = $value;
        }
    }

    /**
     * Gets the models data.
     *
     * @return array
     */
    public function data()
    {
        $data = [];
        foreach (array_keys(static::schema()) as $column) {
            if (isset($this->{$column})) {
                $data[$column] = $this->{$column};
            }
        }
        return $data;
    }

    /**
     * Returns the errors array.
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Adds an error for the specified field.
     *
     * @param string $field
     * @param string $message
     */
    public function addError($field, $message)
    {
        if (!array_key_exists($field, $this->errors)) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    /**
     * Validates the model data.
     *
     * @param array $data
     *
     * @return boolean
     */
    public function validates($data = null)
    {
        $this->errors = [];

        // Get data if it wasn't passed
        if ($data === null) {
            $data = static::data();
        }

        foreach (static::$_validates as $field => $validations) {
            Validations::run($this, $field, $validations);
        }

        return count($this->errors) == 0;
    }

    /**
     * Saves the model to the database.
     *
     * @return boolean
     */
    public function save()
    {
        // Validate
        if (!$this->validates()) {
            return false;
        }

        // Run filter
        $this->runFilters('before', $this->_isNew ? 'create' : 'save');

        // Get data
        $data = static::data();

        // Create
        if ($this->_isNew) {
            $result = static::connection()
                ->insert($data)
                ->into(static::$_table)
                ->exec();

            $this->id = static::connection()->lastInsertId();

            return $result;
        }
        // Update
        else {
            return static::connection()
                ->update(static::$_table)
                ->set($data)
                ->where(static::$_primaryKey . ' = ?', $data[static::$_primaryKey])
                ->exec();
        }
    }
}
