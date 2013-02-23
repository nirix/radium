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

namespace Radium\Http;

/**
 * Radium's HTTP response class.
 *
 * @since 0.2
 * @package Radium
 * @subpackage HTTP
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Response
{
    public $response;

    /**
     * Creates a new response object.
     *
     * @param mixed $response Response body
     * @param array $vars     Variables to be passed to the response if it is a function
     */
    public function __construct($response, Array $vars = array())
    {
        // Was a function passed?
        if (is_callable($response)) {
            $response = call_user_func_array($response, array_merge(array(Router::$extension), $vars));
        }

        $this->response = $response;
    }

    /**
     * Converts the response to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->response;
    }
}
