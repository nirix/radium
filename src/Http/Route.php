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

namespace Radium\Http;


/**
 * Route class.
 *
 * @since 0.2
 * @package Radium/Http
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Route
{
    public $route;
    public $destination;
    public $method = array('get', 'post');
    public $params = array();
    public $args = array();

    /**
     * Creates a new route.
     *
     * @param string $route URL to route
     */
    public function __construct($route)
    {
        $this->route = $route;
    }

    /**
     * Destination class and method of route.
     *
     * @param string $destination Class and method to route to
     * @param array  $args        Arguments to pass to the routed method
     *
     * @example
     *     to('Admin/Settings.index')
     */
    public function to($destination, array $args = array())
    {
        $this->destination = $destination;
        $this->args        = $args;
        return $this;
    }

    /**
     * HTTP methods to accept.
     *
     * @param mixed $method
     *
     * @example
     *     method('get');
     *     method(array('get', 'post'));
     *
     */
    public function method($method)
    {
        // Convert to an array if needed
        if (!is_array($method)) {
            $method = array($method);
        }

        $this->method = $method;
        return $this;
    }
}
