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

use Radium\Exception as Exception;
use Radium\Error;
use Radium\Util\Inflector;

/**
 * Radium's Router.
 *
 * @since 0.1
 * @package Radium/Http
 * @author Jack Polgar <jack@polgar.id.au>
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
        $uri = "/" . trim($request->pathInfo(), '/');

        // Check if this is root page
        if (isset(static::$routes['root']) and $request->pathInfo() == '/') {
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

                if (in_array(strtolower($request->method()), $route->method)) {
                    return static::setRoute($route);
                }
            }
        }

        // No matches, try 404 route
        if (isset(static::$routes['404'])) {
            return static::set404();
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

        // Get request file extension
        $match = preg_match("#(?<extension>" . implode('|', static::$extensions) . ")?$#", Request::$requestUri, $params);
        if (isset($params['extension'])) {
            static::$routes['404']->params['extension'] = $params['extension'];
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
