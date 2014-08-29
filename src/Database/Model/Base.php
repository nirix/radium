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

namespace Radium\Database\Model;

use Radium\Helpers\Time;

/**
 * Model base class.
 *
 * @package Raidum\Database\Model
 * @since 2.0
 * @author Jack Polgar
 */
class Base
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
    public function __construct(array $data = [], $isNew = true)
    {
        $this->_isNew = $isNew;
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
     * Returns the models primary key.
     *
     * @return string
     */
    public static function primaryKey()
    {
        return static::$_primaryKey;
    }

    protected function timestampsToLocal()
    {
        if (isset($this->created_at)) {
            $this->created_at = Time::gmtToLocal($this->created_at);
        }

        if (isset($this->updated_at)) {
            $this->updated_at = Time::gmtToLocal($this->updated_at);
        }
    }

    /**
     * Set the created_at value.
     */
    protected function setCreatedAt()
    {
        if (!isset($this->created_at)) {
            $this->created_at = 'NOW()';
        }
    }

    /**
     * Set the updated_at value.
     */
    protected function setUpdatedAt()
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
}
