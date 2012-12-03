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
     *      'username' => ['unique', 'maxLength' => 20] // Unique and max 20 characters
     *  ];
     *
     * @var array
     */
    protected static $_validates = [];

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
    protected $isNew;

    /**
     * Table schema.
     *
     * @var array
     */
    protected static $_schema = [];

    /**
     * Filters to process data before certain events.
     *
     * @var array
     */
    protected static $_filtersBefore = [];

    /**
     * Filters to process data after certain events.
     *
     * @var array
     */
    protected static $_filtersAfter  = [];

    /**
     * Model constructor.
     *
     * @param array $data
     * @param bool  $isNew
     */
    public function __construct(array $data = [], $isNew = true)
    {
        // Get table schema
        static::getSchema();

        foreach ($data as $column => $value) {
            $this->{$column} = $value;
        }

        $this->_isNew = $isNew;
    }

    /**
     * Used to fetch the tables schema.
     *
     * @access protected
     */
    protected static function getSchema()
    {
        // Make sure we haven't already fetched
        // the tables schema.
        if (static::$_schema === null) {
            // Describe the table...
            // code goes here...
        }
    }

    /**
     * Returns the connection for the model.
     *
     * @return object
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
            ->select(array_keys(static::$_schema))
            ->from(static::$_table)
            ->model(get_called_class());
    }
}
