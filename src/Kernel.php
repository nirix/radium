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
    protected static $controller;

    /**
     * Runs the application and routes the request.
     *
     * @param object $app Instantiated application object.
     */
    public static function run($app)
    {
        $route = Router::process(new Request);

        // Route to 404 if controller and/or method
        if (!class_exists($route['controller'])
        or !method_exists($route['controller'], "{$route['method']}Action")) {
            $route = Router::set404();
        }

        static::$controller = new $route['controller']();

        // Run before filters
        static::runFilters($route['method'], static::$controller->filtersBefore());

        // Execute action
        if (static::$controller->executeAction) {
            $response = call_user_func_array(
                array(static::$controller, $route['method'] . 'Action'),
                $route['args']
            );
        }

        // Run after filters
        static::runFilters($route['method'], static::$controller->filtersAfter());

        // Shutdown the controller
        static::$controller->__shutdown($response);
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
            static::$controller->{$filter}();
        }
    }

    /**
     * Returns the instantiated controller object.
     *
     * @return object
     */
    public static function controller()
    {
        return static::$controller;
    }

    /**
     * @return string
     */
    public static function version()
    {
        return static::$version;
    }
}
