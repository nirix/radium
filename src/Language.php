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

namespace Radium;

use InvalidArgumentException;
use Radium\Language\Translation;

require __DIR__ . "/Translations/enAU.php";

/**
 * Language class.
 *
 * @since 0.1
 * @package Radium\Language
 * @author Jack Polgar <jack@polgar.id.au>
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
            throw new InvalidArgumentException('Expected \'$language\' to be callable');
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

    /**
     * Sets the specified language as the currently active one.
     *
     * @param string $locale
     */
    public static function setCurrent($locale)
    {
        if (isset(static::$registered[$locale])) {
            static::$current = $locale;
        }
    }

    /**
     * Returns the currently active locale.
     *
     * @return object
     */
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
        return static::current()->translate($string, $vars);
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
