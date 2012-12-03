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
        return '<link rel="stylesheet" href="' . $href . '" screen="' . $media . '">' . PHP_EOL;
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
    public static function link($label, $url = null, array $attributes = [])
    {
        // If the label is null, use the URL
        if ($label === null) {
            $label = $url;
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
