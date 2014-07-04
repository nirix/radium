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

use Radium\Kernel;

/**
 * Radium's HTTP response class.
 *
 * @since 0.2
 * @package Radium/Http
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Response
{
    /**
     * HTTP status code.
     */
    public $status = 200;

    /**
     * Response body.
     */
    public $body;

    /**
     * Response content-type.
     */
    public $contentType = 'text/html';

    /**
     * Response headers.
     */
    protected $headers = array();

    public function __construct($response = null)
    {
        // Anonymous function to configure the response block-style.
        if (is_callable($response)) {
            $response($this);
        }
        // String to be used as the body.
        elseif (is_string($response)) {
            $this->body = $response;
        }
    }

    /**
     * Takes a file extension and sets the content-type.
     *
     * @param string $format
     */
    public function format($format)
    {
        switch ($format) {
            case 'html':
                $this->contentType = 'text/html';
                break;

            case 'json':
                $this->contentType = 'application/json';
                break;
        }
    }

    /**
     * Sets a response header.
     *
     * @param string $header
     * @param string $value
     */
    public function header($header, $value, $replace = true)
    {
        $this->headers[] = array($header, $value, $replace);
    }

    /**
     * Sends the response to the browser.
     */
    public function send()
    {
        // Set response code
        $this->setResponseCode();

        // Set content-type
        header("Content-Type: {$this->contentType}");

        // Set headers
        foreach ($this->headers as $header) {
            header("{$header[0]}: {$header[1]}", $header[2]);
        }

        // Print the content
        print($this->body);
    }

    /**
     * Sets the HTTP response header.
     */
    protected function setResponseCode()
    {
        switch ($this->status) {
            case 100: $text = 'Continue'; break;
            case 101: $text = 'Switching Protocols'; break;
            case 200: $text = 'OK'; break;
            case 201: $text = 'Created'; break;
            case 202: $text = 'Accepted'; break;
            case 203: $text = 'Non-Authoritative Information'; break;
            case 204: $text = 'No Content'; break;
            case 205: $text = 'Reset Content'; break;
            case 206: $text = 'Partial Content'; break;
            case 300: $text = 'Multiple Choices'; break;
            case 301: $text = 'Moved Permanently'; break;
            case 302: $text = 'Moved Temporarily'; break;
            case 303: $text = 'See Other'; break;
            case 304: $text = 'Not Modified'; break;
            case 305: $text = 'Use Proxy'; break;
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;

            default:
                throw new \Exception("Unknown HTTP status code: '{$this->status}'");
                break;
        }

        header("{$_SERVER['SERVER_PROTOCOL']} {$this->status} {$text}");
    }
}
