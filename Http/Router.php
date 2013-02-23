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

use Radium\Exception as Exception;
use Radium\Error;

/**
 * Radium's Router.
 *
 * @since 0.1
 * @package Radium
 * @subpackage HTTP
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Router
{
    private static $routes = array();

    // Routed values
    public static $controller;
    public static $method;
    public static $params = array();
    public static $vars = array();
    public static $extension;
    public static $extensions = array('.json', '.atom');


    /**
     * Sets the root route.
     */
    public static function root()
    {
        return static::$routes['root'] = new Route('root');
    }

    /**
     * Adds a new route.
     *
     * @param string $route URI to route
     */
    public static function route($route)
    {
        // 404 Route
        if ($route == '404') {
            return static::$routes['404'] = new Route('404');
        }

        return static::$routes[] = new Route($route);
    }

    /**
     * Routes the request to the controller.
     *
     * @param Request $request
     */
    public static function process(Request $request)
    {
        $uri = "/" . trim($request->uri(), '/');

        // Check if this is root page
        if (isset(static::$routes['root']) and Request::$uri == '/') {
            return static::setRoute(static::$routes['root']);
        }

        // The fun begins
        foreach (static::$routes as $route) {
            // Does the route match the request?
            $pattern = "#^{$route->route}" . '(?<extension>' . implode('|', static::$extensions) . ")?$#";
            if (preg_match($pattern, $uri, $params)) {
                unset($params[0]);
                $route->params = array_merge($route->params, $params);
                $route->destination = preg_replace($pattern, $route->destination, $uri);

                if (in_array(Request::$method, $route->method)) {
                    return static::setRoute($route);
                }
            }
        }

        // No matches, try 404 route
        if (isset(static::$routes['404'])) {
            return static::setRoute(static::$routes['404']);
        }
        // No 404 route, Exception time! FUN :D
        else {
            Error::halt("Routing Error", "No routes found for '{$uri}'");
        }
    }

    /**
     * Sets the route info to that of the 404 route.
     */
    public static function set404()
    {
        if (!isset(static::$routes['404'])) {
            Error::halt("Route Error", "There is no 404 route set.");
        }
        return static::setRoute(static::$routes['404']);
    }

    private static function setRoute($route)
    {
        $destination = explode('.', $route->destination);
        $method = explode('/', implode('.', array_slice($destination, 1)));
        $vars = isset($method[1]) ? explode(',', $method[1]) : array();

        static::$controller = str_replace('::', "\\", $destination[0]);
        static::$method = $method[0];
        static::$params = $route->params;
        static::$vars = $vars;
        static::$extension = (isset($route->params['extension']) ? $route->params['extension'] : 'html');

        // Remove the first dot from the extension
        if (static::$extension[0] == '.') {
            static::$extension = substr(static::$extension, 1);
        }
    }
}
