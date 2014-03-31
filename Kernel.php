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

namespace Radium;

use Radium\Http\Router;
use Radium\Http\Request;
use Radium\Http\Response;

/**
 * Radium's Kernel, the heart of it all.
 *
 * @since 0.1
 * @package Radium
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Kernel
{
    protected static $version = '2.0.0';

    /**
     * Runs the application and routes the request.
     *
     * @param object $app Instantiated application object.
     */
    public static function run($app)
    {
        $route = Router::process(new Request);

        $controller = new $route['controller']();

        // Run before filters
        static::runFilters($route['method'], $controller->filtersBefore());

        // Execute action
        if ($controller->executeAction) {
            $response = call_user_func_array(
                array($controller, $route['method'] . 'Action'),
                $route['args']
            );
        }

        // Run after filters
        static::runFilters($route['method'], $controller->filtersBefore());

        // If the action returned something, pass it back to the application
        if ($response !== null) {
            // Response object
            if (is_object($response)) {
                $controller->response = $response;
            }
            // Plain text
            else {
                $controller->response->body = $response;
            }
        }

        // Shutdown the controller
        $controller->__shutdown();
    }

    /**
     * Runs the filters for the specified action.
     *
     * @param string $action  Routed method/action.
     * @param array  $filters Array containing controller filters.
     */
    protected static function runFilters($action, $filters)
    {
        $filters = array_merge(
            $filters['*'],
            isset($filters[$action]) ? $filters[$action] : array()
        );

        foreach ($filters as $filter) {
            static::$app->{$filter}();
        }
    }

    /**
     * @return string
     */
    public static function version()
    {
        return static::$version;
    }
}
