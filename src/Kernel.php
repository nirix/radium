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

use Exception;
use ReflectionMethod;
use Radium\Action\Controller;
use Radium\EventDispatcher;
use Radium\Routing\Router;
use Radium\Http\Request;
use Radium\Http\Response;

/**
 * Radium's Kernel, the heart of it all.
 *
 * @since 0.1
 * @package Radium
 * @author Jack Polgar <jack@polgar.id.au>
 */
class Kernel
{
    const VERSION = '2.0.0-dev';

    /**
     * @var Controller
     */
    protected static $controller;

    /**
     * Runs the application and routes the request.
     *
     * @param object $app Instantiated application object.
     */
    public static function run($app)
    {
        $route = Router::process(new Request);

        // Route to 404 if controller and/or method cannot be found
        if (!class_exists($route['controller'])
        or !method_exists($route['controller'], "{$route['method']}Action")) {
            if ($app->environment() == 'development') {
                throw new Exception("Unable to find controller for '" . Request::pathInfo() . "'");
            } else {
                $route = Router::set404();
            }
        }

        // Get method parameters
        $r = new ReflectionMethod("{$route['controller']}::{$route['method']}Action");
        $params = [];

        foreach ($r->getParameters() as $param) {
            if (isset($route['params'][$param->getName()])) {
                $params[] = $route['params'][$param->getName()];
            }
        }
        unset($r, $param);

        static::$controller = new $route['controller']();

        // Run before filters
        $beforeAll    = EventDispatcher::dispatch("before." . $route['controller'] . "::*");
        $beforeAction = EventDispatcher::dispatch("before." . $route['controller'] . "::{$route['method']}");

        if ($beforeAll instanceof Response || $beforeAction instanceof Response) {
            static::$controller->executeAction = false;
            $response = $beforeAll ?: $beforeAction;
        }

        // Execute action
        if (static::$controller->executeAction) {
            $response = call_user_func_array(
                array(static::$controller, $route['method'] . 'Action'),
                $params
            );
        }

        // Run after filters
        $afterAll    = EventDispatcher::dispatch("after." . $route['controller'] . "::*");
        $afterAction = EventDispatcher::dispatch("after." . $route['controller'] . "::{$route['method']}");

        if ($afterAll instanceof Response || $afterAction instanceof Response) {
            $response = $afterAll instanceof Response ? $afterAll : $afterAction;
        }

        // Send response
        if (!$response instanceof Response) {
            throw new Exception("The controller [{$route['controller']}::{$route['method']}] returned an invalid response.");
        } else {
            $response->send();
        }

        // Shutdown the controller
        static::$controller->__shutdown();
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
