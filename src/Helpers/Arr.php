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

namespace Radium\Helpers;

/**
 * Radium array helper.
 *
 * @author Jack P.
 * @package Radium\Helpers
 * @since 2.0
 */
class Arr
{
    /**
     * Converts the given data to an array.
     *
     * @param mixed $data
     *
     * @return array
     */
    public static function convert($data)
    {
        // Is it an object with a __toArray() method?
        if (is_object($data) and method_exists($data, '__toArray')) {
            // Hell yeah, we don't need to do anything.
            return $data->__toArray();
        }
        // Just an object, take its variables!
        elseif (is_object($data)) {
            // Create an array
            $array = array();

            // Loop over the classes variables
            foreach (get_class_vars($data) as $var => $val) {
                // And steal them! MY PRECIOUS!
                $array[$var] = $val;
            }

            // And return the array.
            return $array;
        }
        // Array containing other things?
        elseif (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = static::convert($v);
            }
        }

        return $data;
    }

    /**
     * Removes the specified keys from the array.
     *
     * @param array $array
     * @param array $keys Keys to remove
     *
     * @return array
     */
    public static function removeKeys($array, $keys)
    {
        // Loop over the array
        foreach ($array as $key => $value) {
            // Check if we want to remove it...
            if (!is_numeric($key) and in_array($key, $keys)) {
                unset($array[$key]);
                continue;
            }

            // Filter the value if it's an array also
            $array[$key] = is_array($value) ? static::removeKeys($value, $keys) : $value;
        }

        return $array;
    }
}
