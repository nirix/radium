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

use Radium\Database;
use Radium\Database\Validations;
use Radium\Helpers\Time;
use Radium\Util\Inflector;
use Radium\Core\Hook;
use Radium\Language;

use Radium\Database\Model\Relatable;

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
    use Relatable;

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
     * Field validations.
     *
     * @example
     *  $_validates = [
     *      'username' => ['unique' => true, 'maxLength' => 20] // Unique and max 20 characters
     *  ];
     *
     * @var array
     */
    protected static $_validates = array();

    /**
     * Model errors.
     *
     * @var array
     */
    protected $errors = array();

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
    protected static $_schema = array();

    /**
     * Filters to process data before certain events.
     *
     * @var array
     */
    protected static $_before = array();

    /**
     * Filters to process data after certain events.
     *
     * @var array
     */
    protected static $_after = array();

    /**
     * Belongs-to relationships.
     *
     * @var array
     */
    protected static $_belongsTo = array();

    /**
     * Has-many relationships.
     *
     * @var array
     */
    protected static $_hasMany = array();

    /**
     * Cached relationship objects.
     *
     * @var array
     */
    protected $_relationsCache = array();

    /**
     * Model constructor.
     *
     * @param array $data
     * @param bool  $isNew
     */
    public function __construct(array $data = array(), $isNew = true)
    {
        // Set isNew
        $this->_isNew = $isNew;

        // Set defaults
        foreach (static::schema() as $field => $properties) {
            $this->{$field} = $properties['default'];
        }

        // Set data
        foreach ($data as $column => $value) {
            $this->{$column} = $value;
        }

        // Run stuff for rows fetched from the database.
        if (!$isNew) {
            // Convert timestamps to local
            foreach (array('created_at', 'updated_at') as $timestamp) {
                if (array_key_exists($timestamp, $data) and $this->{$timestamp} !== null) {
                    $this->{$timestamp} = Time::gmtToLocal($this->{$timestamp});
                }
            }
        }

        // Add filters
        foreach (array('create', 'save') as $action) {
            if (!array_key_exists($action, static::$_before)) {
                static::$_before[$action] = array();
            }
        }

        // Add before create filters
        if (!in_array('beforeCreateTimestamps', static::$_before['create'])) {
            static::$_before['create'][] = 'beforeCreateTimestamps';
        }

        // Add before save filters
        if (!in_array('beforeSaveTimestamps', static::$_before['save'])) {
            static::$_before['save'][] = 'beforeSaveTimestamps';
        }

        // Run filters
        $this->runFilters('after', 'construct');
    }

    /**
     * Used to fetch the tables schema.
     *
     * @access protected
     */
    protected static function loadSchema()
    {
        // Make sure there's a place to store the schema
        if (!array_key_exists(static::table(), static::$_schema)) {
            static::$_schema[static::table()] = null;
        }

        // Make sure we haven't already fetched
        // the tables schema.
        if (static::$_schema[static::table()] === null) {
            $result = static::connection()->prepare("DESCRIBE `" . static::table() . "`")->exec();
            foreach ($result->fetchAll(\PDO::FETCH_COLUMN) as $column) {
                static::$_schema[static::table()][$column['Field']] = array(
                    'type'    => $column['Type'],
                    'default' => $column['Default'],
                    'null'    => $column['Null'] == 'NO' ? false : true,
                    'key'     => $column['Key'],
                    'extra'   => $column['Extra']
                );
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
        return array_key_exists(static::table(), static::$_schema) ? static::$_schema[static::table()] : null;
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

        return static::select()->where("{$find} = ?", $value)->fetch();
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
    public static function select()
    {
        return static::connection()
            ->select()
            ->from(static::table())
            ->model(get_called_class());
    }

    /**
     * Returns the models primary key.
     *
     * @return string
     */
    public static function primaryKey()
    {
        return static::$_primaryKey;
    }

    /**
     * Returns the models table.
     *
     * @return string
     */
    protected static function table()
    {
        $class = new \ReflectionClass(get_called_class());
        return static::$_table !== null ? static::$_table : Inflector::tablise($class->getShortName());
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
        $data = array();
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
     * Returns the models errors with proper messages.
     *
     * @return array
     */
    public function errorMessages()
    {
        $messages = array();

        // Loop over each field
        foreach ($this->errors as $field => $errors) {
            $messages[$field] = array();

            // Loop over the fields errors
            foreach ($errors as $validation => $error) {
                $vars = array_merge(
                    array('field' => Language::translate($field)),
                    $error
                );
                unset($vars['message']);
                $messages[$field][] = Language::translate($error['message'], $vars);
            }
        }

        return $messages;
    }

    /**
     * Adds an error for the specified field.
     *
     * @param string $field
     * @param string $message
     */
    public function addError($field, $validation, $data)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = array();
        }

        $this->errors[$field][$validation] = $data;
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
        $this->errors = array();

        // Get data if it wasn't passed
        if ($data === null) {
            $data = $this->data();
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
                ->into(static::table())
                ->exec();

            $this->id = static::connection()->lastInsertId();
        }
        // Update
        else {
            $result = static::connection()
                ->update(static::table())
                ->set($data)
                ->where(static::$_primaryKey . ' = ?', $data[static::$_primaryKey])
                ->exec();
        }

        // Run filters
        $this->runFilters('after', $this->_isNew ? 'create' : 'save');

        return $result;
    }

    /**
     * Deletes the row from the database.
     *
     * @return boolean
     */
    public function delete()
    {
        // Delete row
        $result = static::connection()
            ->delete()
            ->from(static::table())
            ->where(static::$_primaryKey . " = ?", $this->{static::$_primaryKey})
            ->limit(1)
            ->exec();

        // Run filters
        if ($result) {
            $this->runFilters('after', 'delete');
        }

        return $result;
    }

    /**
     * Set the created_at value.
     */
    protected function beforeCreateTimestamps()
    {
        if (!isset($this->created_at)) {
            $this->created_at = 'NOW()';
        }
    }

    /**
     * Set the updated_at value.
     */
    protected function beforeSaveTimestamps()
    {
        // Convert created_at back to GMT for saving
        if (isset($this->created_at)) {
            $this->created_at = Time::localToGmt($this->created_at);
        }

        // Set updated at
        if (!isset($this->updated_at) or $this->updated_at === null) {
            $this->updated_at = 'NOW()';
        }
    }

    /**
     * Returns the models properties in a key => value array.
     *
     * @return array
     */
    public function __toArray()
    {
        $data = array();

        foreach (static::schema() as $field => $options) {
            $data[$field] = $this->{$field};
        }

        return $data;
    }
}
