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

use Radium\Util\Inflector;

/**
 * Relations model trait.
 *
 * @package Radium\Database\Model
 * @since 2.0
 * @author Jack Polgar
 */
trait Relatable
{
    /**
     * Returns an array containing information about the
     * relation.
     *
     * @param string $name Relation name
     * @param array  $info Relation info
     *
     * @return array
     */
    public static function getRelationInfo($name, $info = array())
    {
        // Get current models namespace
        $class = new \ReflectionClass(get_called_class());
        $namespace = $class->getNamespaceName();

        // Name
        $info['name'] = $name;

        // Model and class
        if (!isset($info['model'])) {
            // Model
            $info['model'] = Inflector::modelise($name);
        }

        // Set model namespace
        if (strpos($info['model'], '\\') === false) {
            $info['model'] = "\\{$namespace}\\{$info['model']}";
        }

        // Class
        $model = new \ReflectionClass($info['model']);
        $info['class'] = $model->getShortName();

        return $info;
    }

    /**
     * We'll use this to handle relationships.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws BadMethodCallException if no relationship is found.
     *
     * @return mixed
     */
    public function __call($method, array $arguments = array())
    {
        if (isset($this->_relationsCache[$method])) {
            return $this->_relationsCache[$method];
        }

        // Belongs-to relationships
        if (isset(static::$_belongsTo[$method])) {
            return $this->_relationsCache[$method] = $this->belongsTo($method, static::$_belongsTo[$method]);
        } else if (in_array($method, static::$_belongsTo)) {
            return $this->_relationsCache[$method] = $this->belongsTo($method);
        }

        // Has-many relationships
        if (isset(static::$_hasMany[$method])) {
            return $this->hasMany($method, static::$_hasMany[$method]);
        } else if (in_array($method, static::$_hasMany)) {
            return $this->hasMany($method);
        }

        // Mad method call
        throw new \BadMethodCallException;
    }

    /**
     * Returns the owning object.
     *
     * @param string $model   Name of the model.
     * @param aray   $options Optional relation options.
     *
     * @return object
     */
    public function belongsTo($model, $options = array())
    {
        if (isset($this->_relationsCache[$model])) {
            return $this->_relationsCache[$model];
        }

        $options = static::getRelationInfo($model, $options);

        if (!isset($options['localKey'])) {
            $options['localKey'] = Inflector::foreignKey($model);
        }

        if (!isset($options['foreignKey'])) {
            $options['foreignKey'] = $options['model']::primaryKey();
        }

        return $this->_relationsCache[$model] = $options['model']::select()
            ->where("{$options['foreignKey']} = ?", $this->{$options['localKey']})
            ->fetch();
    }

    /**
     * Returns an array of owned objects.
     *
     * @param string $model   Name of the model.
     * @param aray   $options Optional relation options.
     *
     * @return array
     */
    public function hasMany($model, $options = array())
    {
        $options = static::getRelationInfo($model, $options);

        if (!isset($options['localKey'])) {
            $options['localKey'] = static::primaryKey();
        }

        if (!isset($options['foreignKey'])) {
            $options['foreignKey'] = Inflector::foreignKey(static::table());
        }

        return $options['model']::select()
            ->where("{$options['foreignKey']} = ?", $this->{$options['localKey']})
            ->mergeNextWhere();
    }
}
