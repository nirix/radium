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
    public $requestUri;
    public $uri;
    public $method;
    public $request = array();
    public $post = array();
    public $scheme;
    public $host;

    protected $requestedWith;
    protected $base;
    protected $segments = array();

    public function __construct()
    {
        // Set request scheme
        $this->scheme = $this->isSecure() ? 'https' : 'http';

        // Set host
        $this->host = strtolower(preg_replace('/:\d+$/', '', trim($_SERVER['SERVER_NAME'])));

        // Set base url
        $this->base = $this->baseUrl();

        // Set the request path
        $this->requestUri = $this->requestUri();

        // Set relative uri
        $this->uri = str_replace($this->base, '', $this->requestUri);

        // Request segments
        $this->segments = explode('/', trim($this->uri, '/'));

        // Set the request method
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        // Requested with
        $this->requestedWith = @$_SERVER['HTTP_X_REQUESTED_WITH'];

        // _REQUEST
        $this->request = $_REQUEST;

        // _POST
        $this->post = $_POST;
    }

    /**
     * Returns the URI.
     *
     * @return string
     */
    public function uri()
    {
        return $this->uri;
    }

    /**
     * Check if the request matches the specified pattern.
     *
     * @param string $pattern
     *
     * @return boolean
     */
    public function matches($pattern)
    {
        if (preg_match("#^{$pattern}?$#", $this->uri)) {
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
    public function post($key, $fallBack = null)
    {
        return isset($this->post[$key]) ? $this->post[$key] : $fallBack;
    }

    /**
     * Gets the URI segment.
     *
     * @param integer $segment Segment index
     *
     * @return mixed
     */
    public function seg($segment)
    {
        return (isset($this->segments[$segment]) ? $this->segments[$segment] : false);
    }

    /**
     * Redirects to the specified URL.
     *
     * @param string $url
     */
    public function redirect($url)
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
    public function redirectTo($path)
    {
        $this->redirect($this->base($path));
    }

    /**
     * Checks if the request was made via Ajax.
     *
     * @return boolean
     */
    public function isAjax()
    {
        return strtolower($this->requestedWith) == 'xmlhttprequest';
    }

    /**
     * Gets the base URL
     *
     * @return string
     */
    public function base($path = '')
    {
        return $this->base . '/' . trim($path, '/');
    }

    /**
     * Determines if the request is secure.
     *
     * @return boolean
     */
    public function isSecure()
    {
        if (!isset($_SERV['HTTPS']) or empty($_SERVER['HTTPS'])) {
            return false;
        }

        return $_SERVER['HTTPS'] == 'on' or $_SERVER['HTTPS'] == 1;
    }

    /**
     * Returns the base URI.
     *
     * @return string
     */
    protected function baseUrl()
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
    protected function requestUri()
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

            $schemeAndHost = $this->scheme . '://' . $this->host;
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
