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

namespace Radium\Http;

use Radium\Exception as Exception;
use Radium\Error;
use Radium\Util\Inflector;

/**
 * Radium's Router.
 *
 * @since 0.1
 * @package Radium/Http
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Router
{
    // Current route
    protected static $currentRoute;

    // Registered routes
    protected static $routes = array();

    // Registered tokens
    protected static $tokens = array(
        ':id' => "(?<id>\d+)"
    );

    // Extensions
    public static $extensions = array('.json', '.atom');

    /**
     * Closure style routing.
     *
     * @example
     *     Router::map(funtion($r)) {
     *         $r->root('Controller.action');
     *     });
     */
    public static function map($block)
    {
        $block(new static);
    }

    /**
     * Sets the root route.
     *
     * @param string $to Controller to route the root URL to.
     */
    public static function root($to = null)
    {
        static::$routes['root'] = new Route('root');

        if ($to) {
            static::$routes['root']->to($to);
        }

        return static::$routes['root'];
    }

    /**
     * Shortcut for `Router::route(...)->method('get')`
     *
     * @param string $route
     */
    public static function get($route)
    {
        $route = new Route($route);
        return static::$routes[] = $route->method('get');
    }

    /**
     * Shortcut for `Router::route(...)->method('post')`
     *
     * @param string $route
     */
    public static function post($route)
    {
        $route = new Route($route);
        return static::$routes[] = $route->method('post');
    }

    /**
     * Shortcut for setting up the routes for a resource.
     *
     * @param string $resource   Resource/model name.
     * @param string $controller Controller to use for the resource.
     */
    public static function resources($resource, $controller)
    {
        $uri = strtolower(Inflector::controllerise($resource));

        // Index, show
        static::get("/{$uri}")->to("{$controller}::index");
        static::get("/{$uri}/(?P<id>[0-9]+)")->to("{$controller}::show", array('id'));

        // New
        static::get("/{$uri}/new")->to("{$controller}::new");
        static::post("/{$uri}/new")->to("{$controller}::create");

        // Edit
        static::get("/{$uri}/(?P<id>[0-9]+)/edit")->to("{$controller}::edit", array('id'));
        static::post("/{$uri}/(?P<id>[0-9]+)/edit")->to("{$controller}::save", array('id'));

        // Delete
        static::get("/{$uri}/(?P<id>[0-9]+)/delete")->to("{$controller}::delete", array('id'));
        static::post("/{$uri}/(?P<id>[0-9]+)/delete")->to("{$controller}::destroy", array('id'));
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
        if (isset(static::$routes['root']) and $request->uri() == '/') {
            return static::setRoute(static::$routes['root']);
        }

        // The fun begins
        foreach (static::$routes as $route) {
            // Replace tokens
            $route->route = str_replace(array_keys(static::$tokens), array_values(static::$tokens), $route->route);

            // Does the route match the request?
            $pattern = "#^{$route->route}" . '(?<extension>' . implode('|', static::$extensions) . ")?$#";
            if (preg_match($pattern, $uri, $params)) {
                unset($params[0]);
                $route->params = array_merge($route->params, $params);
                $route->destination = preg_replace($pattern, $route->destination, $uri);

                // Routed method arguments
                foreach ($route->args as $index => $arg) {
                    if (($arg !== true and $arg !== false) and isset($params[$arg])) {
                        $route->args[$index] = $params[$arg];
                    }
                }

                if (in_array($request->method(), $route->method)) {
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

    /**
     * Registers a token to replace in routes.
     *
     * @param string $token Token name
     * @param string $value Regex value
     *
     * @example
     *     Router::registerToken('post_id', "(?P<post_id>[0-9]+)");
     */
    public static function registerToken($token, $value)
    {
        static::$tokens[":{$token}"] = $value;
    }

    /**
     * Returns the current route.
     *
     * @return object
     */
    public static function currentRoute()
    {
        return static::$currentRoute;
    }

    protected static function setRoute($route)
    {
        $destination = explode('::', $route->destination);

        $info = array(
            'controller' => $destination[0],
            'method'     => $destination[1],
            'params'     => $route->params,
            'args'       => $route->args,
            'extension'  => (isset($route->params['extension']) ? $route->params['extension'] : 'html')
        );

        // Remove the first dot from the extension
        if ($info['extension'][0] == '.') {
            $info['extension'] = substr($info['extension'], 1);
        }

        return static::$currentRoute = $info;
    }
}
