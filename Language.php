<?php
/*!
 * Radium
 * Copyright (C) 2011-2013 Jack P.
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

use Radium\Language\Translation;

require __DIR__ . "/Translations/enAU.php";

/**
 * Language class.
 *
 * @since 0.1
 * @package Radium
 * @subpackage Language
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Language
{
    protected static $link;
    protected static $registered;
    protected static $current = 'enAU';

    /**
     * Registers a new translation.
     *
     * @param function $language
     */
    public function __construct($language)
    {
        static::$link = $this;

        if (!is_callable($language)) {
            throw new \Radium\Exception("Unable to call '\$language'");
        }

        // Create translation
        $translation = new Translation();
        $language($translation);

        // Register translation
        if (!isset(static::$registered[$translation->locale])) {
            static::$registered[$translation->locale] = $translation;
        }
        // Merge strings
        else {
            static::$registered[$translation->locale]->strings = array_merge(
                static::$registered[$translation->locale]->strings,
                $translation->strings
            );
        }
    }

    public static function set($locale)
    {
        if (isset(static::$registered[$locale])) {
            static::$current = $locale;
        }
    }

    public static function current()
    {
        return static::$registered[static::$current];
    }

    /**
     * Translates the string.
     *
     * @param string $string
     * @param array  $vars
     *
     * @return string
     */
    public static function translate($string, $vars = array())
    {
        return call_user_func_array(array(static::current(), 'translate'), func_get_args());
    }

    /**
     * Date localization method.
     *
     * @param string $format
     * @param mixed  $timestamp
     */
    public static function date($format, $timestamp = null)
    {
        return call_user_func_array(array(static::current(), 'date'), func_get_args());
    }

    /**
     * Returns a link to itself.
     *
     * @return object
     */
    public static function link()
    {
        return static::$link;
    }
}
