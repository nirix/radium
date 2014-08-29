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
use Radium\Database\Model\Base;
use Radium\Database\Model\Errors;
use Radium\Database\Model\Filterable;
use Radium\Database\Model\Relatable;
use Radium\Database\Model\Validatable;
use Radium\Database\Validations;
use Radium\Hook;
use Radium\Language;
use Radium\Util\Inflector;

/**
 * Database Model class
 *
 * @package Radium\Database
 * @since 0.1
 * @author Jack P. <nrx@nirix.net>
 * @copyright Copyright (c) Jack P.
 */
class Model extends Base
{
    use Errors;
    use Filterable;
    use Relatable;
    use Validatable;

    /**
     * Model constructor.
     *
     * @param array $data
     * @param bool  $isNew
     */
    public function __construct(array $data = [], $isNew = true)
    {
        parent::__construct($data, $isNew);

        // Set defaults
        foreach (static::schema() as $field => $properties) {
            $this->{$field} = $properties['default'];
        }

        // Set data
        foreach ($data as $column => $value) {
            $this->{$column} = $value;
        }

        // Add filters
        foreach (array('create', 'save') as $action) {
            if (!array_key_exists($action, static::$_before)) {
                static::$_before[$action] = array();
            }
        }

        // Add timestamp filters
        $this->addBeforeFilter('create', 'setCreatedAt');
        $this->addBeforeFilter('save', 'setUpdatedAt');
        $this->addAfterFilter('construct', 'timestampsToLocal');

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
