<?php
/*!
 * Radium
 * Copyright (C) 2011-2014 Jack P.
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
