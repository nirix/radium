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

namespace Radium\Routing;

use Exception;
use Radium\Http\Request;
use Radium\Util\Inflector;

/**
 * Radium's Router.
 *
 * @since 0.1
 * @author Jack Polgar <jack@polgar.id.au>
 */
class Router
{
    /**
     * Current route.
     */
    protected static $currentRoute;

    /**
     * Registered routes.
     */
    protected static $routes = [];

    /**
     * Route tokens.
     */
    protected static $tokens = [
        ':id' => "(?<id>\d+)"
    ];

    /**
     * Route extensions.
     */
    public static $extensions = ['.json', '.atom'];

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
     * Returns compiled path for the route.
     *
     * @param string $name   Route name.
     * @param array  $tokens Token values for route.
     *
     * @return string
     *
     * @throws Exception
     */
    public static function generateUrl($name, array $tokens = [])
    {
        if ($name === null) {
            return;
        }

        if (isset(static::$routes[$name])) {
            $route = static::$routes[$name];
        } else {
            foreach (static::$routes as $r) {
                if ($r->name === $name) {
                    $route = $r;
                }
            }
        }

        if (isset($route)) {
            return $route->compilePath($tokens);
        } else {
            throw new Exception("No route with name [{$name}]");
        }
    }

    /**
     * Sets the root route.
     *
     * @param string $to Controller to route the root URL to.
     */
    public static function root($to = null)
    {
        static::$routes['root'] = new Route('root', 'root');

        if ($to) {
            static::$routes['root']->to($to);
        }

        return static::$routes['root'];
    }

    /**
     * Shortcut for `Router::route(...)->method('get')`
     *
     * @param string $route
     * @param string $name  Route name
     */
    public static function get($route, $name = null)
    {
        return static::route($route, $name)->method('get');
    }

    /**
     * Shortcut for `Router::route(...)->method('post')`
     *
     * @param string $route
     * @param string $name  Route name
     */
    public static function post($route, $name = null)
    {
        return static::route($route, $name)->method('post');
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
        static::get("/{$uri}/:id")->to("{$controller}::show");

        // New
        static::get("/{$uri}/new")->to("{$controller}::new");
        static::post("/{$uri}/new")->to("{$controller}::create");

        // Edit
        static::get("/{$uri}/:id/edit")->to("{$controller}::edit");
        static::post("/{$uri}/:id/edit")->to("{$controller}::save");

        // Delete
        static::get("/{$uri}/:id/delete")->to("{$controller}::delete");
        static::post("/{$uri}/:id/delete")->to("{$controller}::destroy");
    }

    /**
     * Adds a new route.
     *
     * @param string $route URI to route
     * @param string $name  Route name
     */
    public static function route($route, $name = null)
    {
        // 404 Route
        if ($route == '404') {
            return static::$routes['404'] = new Route('404', '404');
        }

        if ($name) {
            return static::$routes[$name] = new Route($route, $name);
        } else {
            return static::$routes[] = new Route($route);
        }
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

                // Merge params with defaults
                $route->params = array_merge($route->defaults, $route->params);

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
            throw new Exception("No routes found for '{$uri}'");
        }
    }

    /**
     * Sets the route info to that of the 404 route.
     */
    public static function set404()
    {
        if (!isset(static::$routes['404'])) {
            throw new Exception("There is no 404 route set.");
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

        $info = [
            'controller' => $destination[0],
            'method'     => $destination[1],
            'params'     => $route->params,
            'defaults'   => $route->defaults,
            'extension'  => (isset($route->params['extension']) ? $route->params['extension'] : 'html')
        ];

        // Remove the first dot from the extension
        if ($info['extension'][0] == '.') {
            $info['extension'] = substr($info['extension'], 1);
        }

        return static::$currentRoute = $info;
    }
}
