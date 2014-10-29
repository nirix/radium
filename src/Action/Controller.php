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

namespace Radium\Action;

use Exception;
use ReflectionClass;
use Radium\Kernel;
use Radium\Error;
use Radium\EventDispatcher;
use Radium\Http\Router;
use Radium\Http\Request;
use Radium\Http\Response;
use Radium\Language;
use Radium\Database;
use Radium\Templating\View;

/**
 * Controller
 *
 * @since 0.3
 * @package Radium/Action
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Controller
{
    /**
     * Name of the layout to render.
     */
    public $layout = 'default.phtml';

    /**
     * The view to be rendered.
     */
    public $view;

    /**
     * Whether or not to execute the routed action.
     */
    public $executeAction = true;

    /**
     * Sets the request, route, database, view and response variables.
     */
    public function __construct()
    {
        $route = Router::currentRoute();

        // Set database connection
        $this->db = Database::connection();
    }

    /**
     * Sends the variable to the view.
     *
     * @param string $name  Variable name.
     * @param mixed  $value Value.
     */
    public function set($name, $value = null)
    {
        View::addGlobal($name, $value);
    }

    /**
     * Renders a response.
     *
     * @param string $view   View to render.
     * @param array  $locals Variables for the view.
     */
    public function render($view, array $locals = [])
    {
        $locals = $locals + [
            '_layout' => $this->layout
        ];

        return new Response(function($resp) use ($view, $locals) {
            $resp->body = $this->renderView($view, $locals);
        });
    }

    /**
     * Renders the view.
     *
     * @param string $view   View to render.
     * @param array  $locals Variables for the view.
     *
     * @return string
     */
    public function renderView($view, array $locals = [])
    {
        $content = View::render($view, $locals);

        if (isset($locals['_layout']) && $locals['_layout']) {
            $content = $this->renderView("layouts/{$locals['_layout']}", [
                'content' => $content
            ]);
        }

        return $content;
    }

    /**
     * Translates the passed string.
     *
     * @param string $string       String to translate.
     * @param array  $replacements Replacements to be inserted into the string.
     */
    public function translate($string, array $replacements = array())
    {
        return Language::translate($string, $replacements);
    }

    /**
     * Redirects to the specified path.
     */
    public function redirectTo($path)
    {
        Request::redirectTo($path);
    }

    /**
     * Easily respond to different request formats.
     *
     * @param callable $func
     *
     * @return object
     */
    public function respondTo($func)
    {
        $route    = Router::currentRoute();
        $response = $func($route['extension'], $this);

        if ($response === null) {
            return $this->show404();
        }

        return $response;
    }

    /**
     * Sets the response to a 404 Not Found
     */
    public function show404()
    {
        $this->executeAction = false;
        return $this->response = new Response(function($resp){
            $resp->status = 404;
            $resp->body   = $this->renderView('errors/404', [
                '_layout' => $this->layout
            ]);
        });

        return new Response(function($resp){
            $resp->status = 404;
        });
    }

    /**
     * Add before filter.
     *
     * @param string $action
     * @param mixed  $callback
     *
     * @example
     *     $this->before('create', 'checkPermission'); // calls the controllers `checkPermission` method
     *     $this->before('create', [$currentUser, 'checkPermission']); // calls `$currentUser->checkPermission()`
     *     $this->before('create', function(){
     *         // Calls the closure / anonymous function
     *     });
     */
    protected function before($action, $callback)
    {
        $this->addFilter('before', $action, $callback);
    }

    /**
     * Add before filter.
     *
     * @param string $action
     * @param mixed  $callback
     */
    protected function after($action, $callback)
    {
        $this->addFilter('after', $action, $callback);
    }

    /**
     * Adds the filter to the event dispatcher.
     *
     * @param string $when     Either 'before' or 'after'
     * @param string $action
     * @param mixed  $callback
     */
    protected function addFilter($when, $action, $callback)
    {
        if (!is_callable($callback) && !is_array($callback)) {
            $callback = [$this, $callback];
        }

        if (is_array($action)) {
            foreach ($action as $method) {
                $this->addFilter($when, $method, $callback);
            }
        } else {
            EventDispatcher::addListener("{$when}." . get_called_class() . "::{$action}", $callback);
        }
    }

    /**
     * Handles controller shutdown.
     */
    public function __shutdown() {}
}
