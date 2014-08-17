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

namespace Radium\Helpers;

use Radium\Http\Request;

/**
 * HTML Helper
 *
 * @author Jack P.
 * @package Radium
 * @subpackage Helpers
 */
class HTML
{
    /**
     * Creates a CSS link tag.
     *
     * @param string $href  CSS file location
     * @param string $media Media type
     *
     * @return string
     */
    public static function cssLinkTag($href, $media = 'screen')
    {
        if (strpos($href, 'http') === false and strpos($href, '//') === false) {
            $href = Request::base($href);
        }
        return '<link rel="stylesheet" href="' . $href . '" media="' . $media . '">' . PHP_EOL;
    }

    /**
     * Returns the code to include a JavaScript file.
     *
     * @param string $file The path to the JavaScript file
     *
     * @return string
     */
    public static function jsIncTag($path)
    {
        if (strpos($path, 'http') === false and strpos($path, '//') === false) {
            $path = Request::base($path);
        }
        return '<script src="' . $path . '" type="text/javascript"></script>' . PHP_EOL;
    }

    /**
     * Returns the code for a link.
     *
     * @param string $label   The label
     * @param string $url     The URL
     * @param array  $options Options for the URL code (class, title, etc)
     *
     * @return string
     */
    public static function link($label, $url = null, array $attributes = array())
    {
        // If the label is null, use the URL
        if ($label === null) {
            $label = $url;
        }

        // If the URL parameter is an object, call its `href()` method.
        if (is_object($url) and method_exists($url, 'href')) {
            $url = $url->href();
        }

        // Is this a local link?
        if (substr($url, 0, 4) != 'http') {
            $url = Request::base(ltrim($url, '/'));
        }

        $attributes['href'] = $url;
        $attributes = static::buildAttributes($attributes);

        return "<a {$attributes}>{$label}</a>";
    }

    /**
     * Returns the code for an image.
     *
     * @param string $url
     * @param array  $attributes Element attributes
     */
    public static function image($url, array $attributes = array())
    {
        $defaults = array(
            'src' => $url,
            'alt' => ''
        );

        $attributes = static::buildAttributes($attributes + $defaults);
        return "<img {$attributes}>";
    }

    /**
     * Returns the code for a link unless the current request matches the URL.
     *
     * @param string $label   The label
     * @param string $url     The URL
     * @param array  $options Options for the URL code (class, title, etc)
     *
     * @return string
     */
    public static function LinkToUnlessCurrent($label, $url, array $attributes = array())
    {
        if (Request::matches($url)) {
            return $label;
        } else {
            return static::link($label, $url, $attributes);
        }
    }

    /**
     * Builds the attributes for HTML elements.
     *
     * @param array $attributes An array of attributes and their values
     *
     * @return string
     */
    public static function buildAttributes($attributes)
    {
        $options = array();
        foreach ($attributes as $attr => $val) {
            $options[] = "{$attr}=\"{$val}\"";
        }
        return implode(' ', $options);
    }
}
