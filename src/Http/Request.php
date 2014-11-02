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

/**
 * Radium's HTTP request class.
 *
 * @since 0.1
 * @package Radium/Http
 * @author Jack Polgar <jack@polgar.id.au>
 */
class Request
{
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND             = 302;
    const HTTP_SEE_OTHER         = 303;

    /**
     * @var Request
     */
    protected static $instance;

    /**
     * @var array
     */
    public static $request = [];

    /**
     * @var array
     */
    public static $get = [];

    /**
     * @var array
     */
    public static $post = [];

    /**
     * @var array
     */
    public static $server;

    /**
     * @var array
     */
    public static $files;

    /**
     * @var array
     */
    public static $headers;

    /**
     * @var string
     */
    public static $pathInfo;

    /**
     * @var string
     */
    public static $requestUri;

    /**
     * @var string
     */
    public static $baseUrl;

    /**
     * @var string
     */
    public static $basePath;

    /**
     * @var string
     */
    public static $method;

    public function __construct()
    {
        static::$instance = $this;

        static::$request = $_REQUEST;
        static::$get     = $_GET;
        static::$post    = $_POST;
        static::$server  = $_SERVER;
        static::$headers = static::getAllHeaders();

        static::$requestUri = static::prepareRequestUri();
        static::$baseUrl    = static::prepareBaseUrl();
        static::$basePath   = static::prepareBasePath();
        static::$pathInfo   = static::preparePathInfo();
        static::$method     = static::$server['REQUEST_METHOD'];
    }

    /**
     * @param string $key
     * @param mixed  $fallback
     */
    public static function server($key, $fallback = '')
    {
        return isset(static::$server[$key]) ? static::$server[$key] : $fallback;
    }

    /**
     * @param string $key
     * @param mixed  $fallback
     */
    public static function get($key, $fallback = '')
    {
        return isset(static::$get[$key]) ? static::$get[$key] : $fallback;
    }

    /**
     * @param string $key
     * @param mixed  $fallback
     */
    public static function request($key, $fallback = '')
    {
        return isset(static::$request[$key]) ? static::$request[$key] : $fallback;
    }

    /**
     * @param string $key
     * @param mixed  $fallback
     */
    public static function post($key, $fallback = '')
    {
        return isset(static::$post[$key]) ? static::$post[$key] : $fallback;
    }

    /**
     * @param string $key
     * @param mixed  $fallback
     */
    public static function header($key, $fallback = '')
    {
        return isset(static::$headers[$key]) ? static::$headers[$key] : $fallback;
    }

    /**
     * @param string  $url
     * @param integer $responseCode
     */
    public static function redirect($url, $responseCode = Request::HTTP_SEE_OTHER)
    {
        header("Location: {$url}", true, $responseCode);
        exit;
    }

    /**
     * @param string  $url
     * @param integer $responseCode
     */
    public static function redirectTo($url, $responseCode = Request::HTTP_SEE_OTHER)
    {
        static::redirect(static::basePath($url), $responseCode);
    }

    /**
     * @return string
     */
    public static function schemeAndHttpHost()
    {
        return static::scheme() . '://' . static::httpHost();
    }

    /**
     * @return bool
     */
    public static function isSecure()
    {
        $https = static::server('HTTPS');
        return !empty($https) && strtolower($https) !== 'off';
    }

    /**
     * @return string
     */
    public static function scheme()
    {
        return static::isSecure() ? 'https' : 'http';
    }

    /**
     * @return string
     */
    public static function host()
    {
        if (!$host = static::header('Host')) {
            if (!$host = static::server('SERVER_NAME')) {
                $host = static::server('SERVER_ADDR', '');
            }
        }

        $host = strtolower(trim($host));
        $host = preg_replace('/:\d+$/', '', $host);

        return $host;
    }

    /**
     * @return integer
     */
    public static function port()
    {
        if ($host = static::header('Host')) {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                return intval(substr($host, $pos + 1));
            }
        }

        return static::server('SERVER_PORT');
    }

    /**
     * @return string
     */
    public static function httpHost()
    {
        $scheme = static::scheme();
        $host   = static::host();
        $port   = static::port();

        if (($scheme == 'http' && $port == 80) || ($scheme == 'https' && $port == 443)) {
            return $host;
        }

        return $host . ':' . $port;
    }

    /**
     * @return string
     */
    public static function method()
    {
        return static::$server['REQUEST_METHOD'];
    }

    /**
     * @return string
     */
    public static function basePath($append = null)
    {
        return static::$basePath . ($append ? '/' . ltrim($append, '/') : '');
    }

    /**
     * @return string
     */
    public static function pathInfo()
    {
        return static::$pathInfo;
    }

    /**
     * @return string
     */
    public static function requestUri()
    {
        return static::$requestUri;
    }

    /**
     * @return bool
     */
    public static function matches($path)
    {
        return preg_match("#^{$path}$#", static::$pathInfo);
    }

    /**
     * Builds a query string, including the question mark.
     *
     * @param array $data
     *
     * @return string
     */
    public static function buildQueryString(array $data = null, $urlEncode = true)
    {
        if ($data === null) {
            $data = static::$get;
        }

        $query = [];

        foreach ($data as $name => $value) {
            $query[] = "{$name}=" . ($urlEncode ? urlencode($value) : $value);
        }

        if (count($query)) {
            return '?' . implode('&', $query);
        }
    }

    /**
     * Gets headers from `_SERVER` and formats the names nicely.
     *
     * @return array
     */
    protected function getAllHeaders()
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $key = substr($key, 5);
                $key = str_replace('_', ' ', strtolower($key));
                $key = str_replace(' ', '-', ucwords($key));

                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * @return string
     */
    protected static function prepareBaseUrl()
    {
        $fileName = basename(static::$server['SCRIPT_FILENAME']);

        if ($fileName === basename(static::$server['SCRIPT_NAME'])) {
            $baseUrl = static::$server['SCRIPT_NAME'];
        } elseif ($fileName === basename(static::$server['PHP_SELF'])) {
            $baseUrl = static::$server['PHP_SELF'];
        } elseif ($fileName === basename(static::$server['ORIG_SCRIPT_NAME'])) {
            $baseUrl = static::$server['ORIG_SCRIPT_NAME'];
        }

        if (strpos($baseUrl, '?') !== false) {
            $baseUrl = explode('?', $baseUrl)[0];
        }

        return rtrim(str_replace($fileName, '', $baseUrl), '/');
    }

    /**
     * @return string
     */
    protected static function prepareBasePath()
    {
        $fileName = basename(static::$server['SCRIPT_FILENAME']);
        $baseUrl  = static::$baseUrl;

        if (empty($baseUrl)) {
            return '';
        }

        if ($fileName === basename($baseUrl)) {
            $basePath = dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $basePath = str_replace('\\', '/', $basePath);
        }

        return rtrim($basePath, '/');
    }

    /**
     * @return string
     */
    public static function preparePathInfo()
    {
        $requestUri = static::$requestUri;
        $baseUrl    = static::$baseUrl;

        if ($baseUrl === null || $requestUri === null) {
            return '/';
        }

        $pathInfo = '/';

        // Remove the query string
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if (false === $pathInfo = substr($requestUri, strlen($baseUrl))) {
            return $requestUri;
        }

        return $pathInfo;
    }

    /**
     * @return string
     */
    protected static function prepareRequestUri()
    {
        $requestUri = '';

        // Microsoft IIS Rewrite Module
        if (isset(static::$headers['X_ORIGINAL_URL'])) {
            $requestUri = static::$headers['X_ORIGINAL_URL'];
        }
        // IIS ISAPI_Rewrite
        elseif (isset(static::$headers['X_REWRITE_URL'])) {
            $requestUri = static::$header['X_REWRITE_URL'];
        }
        // IIS7 URL Rewrite
        elseif (static::server('IIS_WasUrlRewritten') == '1' && static::server('UNENCODED_URL') != '') {
            $requestUri = static::server('UNENCODED_URL');
        }
        // HTTP proxy, request URI with scheme, host and port + the URL path
        elseif (isset(static::$server['REQUEST_URI'])) {
            $requestUri = static::$server['REQUEST_URI'];
            $schemeAndHttpHost = static::schemeAndHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        }
        // IIS 5, PHP as CGI
        elseif (isset(static::$server['ORIG_PATH_INFO'])) {
            $requestUri = static::$server['ORIG_PATH_INFO'];

            if (static::$queryString != '') {
                $requestUri .= '?' . static::$server['ORIG_PATH_INFO'];
            }
        }

        static::$server['REQUEST_URI'] = $requestUri;
        return $requestUri;
    }

    /**
     * Get the instantiated request instance instead of creating a new one.
     *
     * @return Request
     */
    public static function getInstance()
    {
        return static::$instance;
    }
}
