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
 * Model filters trait.
 *
 * @package Radium\Database\Model
 * @since 2.0
 * @author Jack Polgar
 */
trait Filterable
{
    protected function addBeforeFilter($action, $method)
    {
        if (!isset(static::$_before[$action])) {
            static::$_before[$action] = [];
        }

        if (!in_array($method, static::$_before[$action])) {
            static::$_before[$action][] = $method;
        }
    }

    protected function addAfterFilter($action, $method)
    {
        if (!isset(static::$_after[$action])) {
            static::$_after[$action] = [];
        }

        if (!in_array($method, static::$_after[$action])) {
            static::$_after[$action][] = $method;
        }
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
}
