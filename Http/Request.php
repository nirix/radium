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

/**
 * Radium's HTTP request class.
 *
 * @since 0.1
 * @package Radium/Http
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Request
{
    protected static $current;

    public static $requestUri;
    public static $uri;
    public static $method;
    public static $request = array();
    public static $post = array();
    public static $scheme;
    public static $host;
    public static $query;

    protected static $requestedWith;
    protected static $base;
    protected static $segments = array();

    public function __construct()
    {
        // Set request scheme
        static::$scheme = static::isSecure() ? 'https' : 'http';

        // Set host
        static::$host = strtolower(preg_replace('/:\d+$/', '', trim($_SERVER['SERVER_NAME'])));

        // Set base url
        static::$base = static::getBaseUrl();

        // Set the request path
        static::$requestUri = static::getRequestUri();

        // Set relative uri
        static::$uri = str_replace(static::$base, '', static::$requestUri);

        // Request segments
        static::$segments = explode('/', trim(static::$uri, '/'));

        // Set the request method
        static::$method = strtolower($_SERVER['REQUEST_METHOD']);

        // Requested with
        static::$requestedWith = @$_SERVER['HTTP_X_REQUESTED_WITH'];

        // _REQUEST
        static::$request = $_REQUEST;

        // _POST
        static::$post = $_POST;

        // Set currnet request
        static::$current = $this;

        // Query string
        static::$query = $_SERVER['QUERY_STRING'];
    }

    /**
     * Returns the URI.
     *
     * @return string
     */
    public static function uri()
    {
        return static::$uri;
    }

    /**
     * Check if the request matches the specified pattern.
     *
     * @param string $pattern
     *
     * @return boolean
     */
    public static function matches($pattern)
    {
        if (preg_match("#^{$pattern}?$#", static::$uri)) {
            return true;
        }
        return false;
    }

    /**
     * Returns the value of the key from the POST array,
     * if it's not set, returns null by default.
     *
     * @param string $key     Key to get from POST array
     * @param mixed  $not_set Value to return if not set
     *
     * @return mixed
     */
    public static function post($key, $fallback = null)
    {
        return isset(static::$post[$key]) ? static::$post[$key] : $fallback;
    }

    /**
     * Gets the URI segment.
     *
     * @param integer $segment Segment index
     *
     * @return mixed
     */
    public static function seg($segment)
    {
        return (isset(static::$segments[$segment]) ? static::$segments[$segment] : false);
    }

    /**
     * Redirects to the specified URL.
     *
     * @param string $url
     */
    public static function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    /**
     * Redirects to the specified path relative to the
     * entry file.
     *
     * @param string $path
     */
    public static function redirectTo($path)
    {
        static::redirect(static::base($path));
    }

    /**
     * Checks if the request was made via Ajax.
     *
     * @return boolean
     */
    public static function isAjax()
    {
        return strtolower(static::$requestedWith) == 'xmlhttprequest';
    }

    /**
     * Gets the base URL
     *
     * @return string
     */
    public static function base($path = '')
    {
        return static::$base . '/' . trim($path, '/');
    }

    /**
     * Returns the current request method.
     *
     * @return string
     */
    public static function method()
    {
        return static::$method;
    }

    /**
     * Returns the current requested URI.
     *
     * @return string
     */
    public static function requestUri()
    {
        return static::$requestUri;
    }

    /**
     * Determines if the request is secure.
     *
     * @return boolean
     */
    public static function isSecure()
    {
        if (!isset($_SERV['HTTPS']) or empty($_SERVER['HTTPS'])) {
            return false;
        }

        return $_SERVER['HTTPS'] == 'on' or $_SERVER['HTTPS'] == 1;
    }

    /**
     * Returns the instantiated request object.
     *
     * @return object
     */
    public static function current()
    {
        return static::$current;
    }

    /**
     * Returns the base URI.
     *
     * @return string
     */
    protected static function getBaseUrl()
    {
        $filename = basename($_SERVER['SCRIPT_FILENAME']);

        if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (basename($_SERVER['PHP_SELF']) === $filename) {
            $baseUrl = $_SERVER['PHP_SELF'];
        } elseif (basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['ORIG_SCRIPT_NAME'];
        }

        $baseUrl = rtrim(str_replace($filename, '', $baseUrl), '/');

        return $baseUrl;
    }

    /**
     * Determines the request URI.
     *
     * @return string
     */
    protected static function getRequestUri()
    {
        $requestUri = '';

        if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
            $requestUri = $_SERVER['HTTP_X_ORIGINAL_URL'];
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['IIS_WasUrlRewritten'])
                  and $_SERVER['IIS_WasUrlRewritten'] = 1
                  and isset($_SERVER['UNENCODED_URL'])
                  and $_SERVER['UNENCODED_URL'] != '')
        {
            $requestUri = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];

            $schemeAndHost = static::$scheme . '://' . static::$host;
            if (strpos($requestUri, $schemeAndHost)) {
                $requestUri = substr($requestUri, strlen($schemeAndHost));
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
        }

        // Remove query string
        if (strpos($requestUri, '?') !== false) {
            $requestUri = explode('?', $requestUri);
            $requestUri = $requestUri[0];
        }

        return $requestUri;
    }
}
