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

namespace Radium\Action;

use Radium\Kernel;
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
        Request::redirectTo($pat);
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
        $route      = Router::currentRoute();
        $controller = $this;

        // Set response content-type
        $this->response->format($route['extension']);

        return $func($route['extension'], $controller);
    }

    /**
     * Sets the response to a 404 Not Found
     */
    public function show404()
    {
        $this->setView("Errors/404");
        return $this->response = new Response(function($resp){
            $resp->status = 404;
        });
    }

    /**
     * Filters to run before executing the action.
     *
     * @return array
     */
    public function filtersBefore() {
        return array(
            '*' => array()
        );
    }

    /**
     * Filters to run after executing the action.
     *
     * @return array
     */
    public function filtersAfter() {
        return array(
            '*' => array()
        );
    }

    /**
     * Renders the view and layout then sends the response.
     */
    public function __shutdown()
    {
        $route = Router::currentRoute();

        if ($this->response->contentType !== 'text/html') {
            $this->layout = false;
        }

        // Does the view need to be rendered?
        if ($this->response->body === null and $this->executeAction and $this->view) {
            $this->response->body = $this->render($this->view);
        }

        // Render the layout
        if ($this->layout) {
            $this->response->body = $this->render(
                "Layouts/{$this->layout}",
                array('content' => $this->response->body)
            );
        }

        $this->response->send();
    }
}
