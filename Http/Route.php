<?php
/*!
 * Radium
 * Copyright (C) 2011-2013 Jack P.
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

use Radium\Loader;

/**
 * Route class.
 *
 * @since 0.2
 * @package Radium
 * @subpackage HTTP
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
    public function to($destination, Array $args = array())
    {
        if (strpos('\\', $destination) === false) {
            $destination = Loader::defaultNamespace() . "\\Controllers\\{$destination}";
        }

        $this->destination = str_replace("\\", "::", $destination);
        $this->args = $args;
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
