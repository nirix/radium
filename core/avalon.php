<?php
/*!
 * Avalon
 * Copyright (C) 2011-2012 Jack Polgar
 *
 * This file is part of Avalon.
 *
 * Avalon is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation; version 3 only.
 *
 * Avalon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Avalon. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * The core Avalon class.
 *
 * @author Jack P.
 * @package Avalon
 * @subpackage Core
 */
class Avalon
{
	private static $version = '0.1';
	private static $app;
	
	/**
	 * Initialize the Avalon framework
	 */
	public static function init()
	{
		// Route the request
		Request::process();
		Router::process(Request::url());
	}
	
	/**
	 * Execute the routed controller and method
	 */
	public static function run()
	{
		// Controller
		$namespace = Router::$namespace;
		$controller_name = str_replace('::', '', $namespace) . Router::$controller . "Controller";
		$controller_file = strtolower(APPPATH . "/controllers/" . (Router::$namespace !== null ? str_replace('::', '/', Router::$namespace) . '/' : '') . Router::$controller . '_controller.php');

		// Method
		$method_name = 'action_' . Router::$method;
		$method_args = Router::$args;

		// Load root namespace app controller
		if (file_exists(APPPATH . "/controllers/app_controller.php"))
		{
			require APPPATH . "/controllers/app_controller.php";
		}

		// Load controller namespaces app controllers
		if ($namespace !== null)
		{
			$ns_path = array();
			foreach (explode('::', $namespace) as $ns)
			{
				$ns_path[] = $ns;

				// Check that the file exists...
				$file_path = APPPATH . "/controllers/" . implode('/', $ns_path) . "/app_controller.php";
				if (file_exists($file_path))
				{
					require $file_path;
				}
			}
		}

		// Check if the controller file exists...
		if (file_exists($controller_file))
		{
			require $controller_file;
		}

		// Check for the controller and method
		if (!class_exists($controller_name) or !method_exists($controller_name, $method_name))
		{
			// Load the error controller
			if (!class_exists('ErrorController'))
			{
				require APPPATH . '/controllers/error_controller.php';
			}

			// Set the error controller info
			$controller_name = 'ErrorController';
			$view_path = 'error/404';
			$method_name = 'action_404';
			$method_args = array();
		}

		// Start the app/controller
		static::$app = new $controller_name();

		// Run before filters
		if (is_array(static::$app->_before))
		{
			$filters = array();

			// Before all
			if (isset(static::$app->_before['*']) and is_array(static::$app->_before['*']))
			{
				// Merge them into the filters array
				$filters = array_merge($filters, static::$app->_before['*']);
			}

			// Before certain methods
			if (isset(static::$app->_before[Router::$method]) and is_array(static::$app->_before[Router::$method]))
			{
				// Merge them into the fitlers array
				$filters = array_merge($filters, static::$app->_before[Router::$method]);
			}

			// Execute the filters
			foreach ($filters as $filter)
			{
				static::$app->$filter();
			}
		}

		// Call the method
		if (static::$app->_render['action'])
		{
			call_user_func_array(array(static::$app, $method_name), $method_args);
		}
		
		// Call our custom 'destructor'. Why not use __destruct(): because even
		// after 'die', 'exit', etc is called, __destruct() is still executed.
		if (method_exists(static::$app, '__shutdown'))
		{
			static::$app->__shutdown();
		}
	}
	
	/**
	 * Returns the application object.
	 *
	 * @return object
	 */
	public static function app()
	{
		return static::$app;
	}
	
	/**
	 * Returns the version of the Avalon framework.
	 *
	 * @return string
	 */
	public static function version()
	{
		return static::$version;
	}
}