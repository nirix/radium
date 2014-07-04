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
 * Radium formatting helper.
 *
 * @author Jack P.
 * @package Radium\Helpers
 * @since 2.0
 */
class Format
{
    /**
     * Returns the JSON encoded version of the passed data.
     *
     * @param mixed $data
     * @param array $options
     *
     * @return string
     */
    public static function toJson($data, $options = array())
    {
        // Merge options with defaults
        $defaults = array('hide' => array('password'));
        $options = array_merge($defaults, $options);

        // Convert the data to an array, if possible..
        if (!is_array($data)) {
            $data = Arr::convert($data);
        }

        foreach ($data as $k => $v) {
            $data[$k] = Arr::convert($v);
        }

        // Remove the parts we don't want...
        if (isset($options['hide']) and is_array($data)) {
            $data = Arr::removeKeys($data, $options['hide']);
        }

        return json_encode($data);
    }
}
