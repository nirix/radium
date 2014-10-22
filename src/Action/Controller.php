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
    public $layout = 'default';

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
        $this->setView(get_called_class() . "\\{$route['method']}");

        // Create response
        $this->response = new Response;

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
        View::set($name, $value);
    }

    /**
     * Sets the view.
     *
     * @param string $view
     */
    public function setView($view)
    {
        $this->view = str_replace(
            array("\\", "/"),
            DIRECTORY_SEPARATOR,
            $view
        );
    }

    /**
     * Renders the view.
     *
     * @param string $view   View to render.
     * @param array  $locals Variables for the view.
     *
     * @return string
     */
    public function render($view, array $locals = array())
    {
        return View::render($view, $locals);
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
     * @param closure $func
     *
     * @return object
     */
    public function respondTo($func)
    {
        $route = Router::currentRoute();

        // Set response content-type
        $this->response->format($route['extension']);

        return $func($route['extension'], $this);
    }

    /**
     * Sets the response to a 404 Not Found
     */
    public function show404()
    {
        $this->executeAction = false;
        return $this->response = new Response(function($resp){
            $resp->status = 404;
            $resp->body   = $this->render('Errors/404');
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
     * Renders the view and layout then sends the response.
     *
     * @param mixed $response Response from the routed method
     */
    public function __shutdown($response = null)
    {
        $route = Router::currentRoute();

        // Object that response to the `send` method.
        if (is_object($response)) {
            if (!method_exists($response, 'send')) {
                throw new Exception("The controller did not return a valid response object");
            }

            $this->response = $response;
        }

        // If the response isn't null and not an object add it to the responses body.
        if ($response !== null && !is_object($response)) {
            $this->response->body = $response;
        }

        // Does the view need to be rendered?
        if ($response === null) {
            if ($this->response->body === null and $this->executeAction and $this->view) {
                $this->response->body = $this->render($this->view);
            }
        }

        // Don't render the layout if the content type isn't HTML
        if ($this->response->contentType !== 'text/html') {
            $this->layout = false;
        }

        // Render the layout
        if ($this->layout) {
            $this->response->body = $this->render(
                "Layouts/{$this->layout}",
                ['content' => $this->response->body]
            );
        }

        $this->response->send();
    }
}
