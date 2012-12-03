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

namespace Radium\Core;

use Radium\Http\Router;
use Radium\Http\Request;
use Radium\Output\Body;
use Radium\Output\View;

/**
 * Radium's Kernel, the heart of it all.
 *
 * @since 0.1
 * @package Radium
 * @subpackage Core
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Kernel
{
    private static $version = '0.1';
    private static $app;

    /**
     * Initializes the the kernel and routes the request.
     */
    public static function init()
    {
        session_start();

        // Route the request
        Router::route(new Request);

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

        // Filters
        static::$app->filters();

        // Before filters
        $filters = array_merge(
            isset(static::$app->before['*']) ? static::$app->before['*'] : [],
            isset(static::$app->before[Router::$method]) ? static::$app->before[Router::$method] : []
        );
        foreach ($filters as $filter) {
            static::$app->{$filter}(Router::$method);
        }
        unset($filters, $filter);

        // Call the method
        if (static::$app->render['action']) {
            $output = call_user_func_array([static::$app, Router::$method . 'Action'], Router::$vars);
        }

        // After filters
        $filters = array_merge(
            isset(static::$app->after['*']) ? static::$app->after['*'] : [],
            isset(static::$app->after[Router::$method]) ? static::$app->after[Router::$method] : []
        );
        foreach ($filters as $filter) {
            static::$app->{$filter}(Router::$method);
        }
        unset($filters, $filter);

        // Check if the action returned content
        if ($output !== null) {
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
