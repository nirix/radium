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
use Radium\Output\Body;
use Radium\Output\View;

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
    private static $version = '0.3';
    private static $app;

    /**
     * Initializes the the kernel and routes the request.
     */
    public static function init()
    {
        session_start();

        // Route the request
        Router::process(new Request);

        // Check if the routed controller and method exists
        if (!class_exists(Router::$controller) or !method_exists(Router::$controller, Router::$method . 'Action')) {
            Router::set404();
        }
    }

    /**
     * Executes the routed request.
     */
    public static function run()
    {
        // Start the app
        static::$app = new Router::$controller;

        // Before filters
        static::runFilters('before');

        // Call the method
        if (static::$app->render['action']) {
            $output = call_user_func_array(array(static::$app, Router::$method . 'Action'), Router::$args);
        }

        // After filters
        static::runFilters('after');

        // If an object is returned, use the `response` variable if it's set.
        if (is_object($output)) {
            $output = isset($output->response) ? $output->response : null;
        }

        // Check if we have any content
        if (static::$app->render['action'] and $output !== null) {
            static::$app->render['view'] = false;
            Body::append($output);

            // Get the content, clear the body
            // and append content to a clean slate.
            $content = Body::content();
            Body::clear();
            Body::append($content);
        }

        static::$app->__shutdown();
    }

    /**
     * Runs the filters filters for the app.
     *
     * @param string $type
     */
    protected static function runFilters($type)
    {
        $filters = static::$app->{$type};

        $filters = array_merge(
            isset($filters['*']) ? $filters['*'] : array(),
            isset($filters[Router::$method]) ? $filters[Router::$method] : array()
        );

        foreach ($filters as $filter) {
            static::$app->{$filter}(Router::$method);
        }
    }

    /**
     * Returns the app object.
     *
     * @return object
     */
    public static function app()
    {
        return static::$app;
    }

    /**
     * Returns the version of Avalon.
     *
     * @return string
     */
    public static function version() {
        return static::$version;
    }
}
