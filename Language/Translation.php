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

namespace Radium\Language;

/**
 * Translation class.
 *
 * @since 0.1
 * @package Radium
 * @subpackage Language
 * @author Jack P.
 * @copyright (C) Jack P.
 */
class Translation
{
    public $name;
    public $locale;
    public $strings = array();

    /**
     * Returns the locale information.
     *
     * @return array
     */
    public static function info()
    {
        return static::$info;
    }

    /**
     * Translates the specified string.
     *
     * @return string
     */
    public function translate($string, Array $vars = array())
    {
        return $this->compileString($this->getString($string), $vars);
    }

    /**
     * Date localization method
     *
     * @param string $format
     * @param mixed  $timestamp
     */
    public function date($format, $timestamp = null)
    {
        return Time::date($format, $timestamp);
    }

    /**
     * Fetches the translation for the specified string.
     *
     * @param string $string
     *
     * @return string
     */
    public function getString($string)
    {
        // Exact match?
        if (isset($this->strings[$string])) {
            return $this->strings[$string];
        } else {
            return $string;
        }
    }

    /**
     * Determines which replacement to use for plurals.
     *
     * @param integer $numeral
     *
     * @return integer
     */
    public function calculateNumeral($numeral)
    {
        return ($numeral > 1 or $numeral < -1 or $numeral == 0) ? 1 : 0;
    }

    /**
     * Compiles the translated string with the variables.
     *
     * @example
     *     compileString('{plural:$1, {$1 post|$1 posts}}', array(1));
     *     will become "1 post"
     *
     * @param string $string
     * @param array  $vars
     *
     * @return string
     */
    protected function compileString($string, $vars)
    {
        $translation = $string;

        // Loop through and replace the placeholders
        // with the values from the $vars array.
        $count = 0;
        foreach ($vars as $key => $val) {
            $count++;

            // If array key is an integer,
            // use the counter to avoid clashes
            // with numbered placeholders.
            if (is_integer($key)) {
                $key = $count;
            }

            // Replace placeholder with value
            $translation = str_replace(array("{{$key}}", "{{$count}}"), $val, $translation);
        }

        // Match plural:n,{x, y}
        if (preg_match_all("/{plural:(?<value>-{0,1}\d+)(,|, ){(?<replacements>.*?)}}/i", $translation, $matches)) {
            foreach($matches[0] as $id => $match) {
                // Split the replacements into an array.
                // There's an extra | at the start to allow for better matching
                // with values.
                $replacements = explode('|', $matches['replacements'][$id]);

                // Get the value
                $value = $matches['value'][$id];

                // Check what replacement to use...
                $replacement_id = $this->calculateNumeral($value);
                if ($replacement_id !== false) {
                    $translation = str_replace($match, $replacements[$replacement_id], $translation);
                }
                // Get the last value then
                else {
                    $translation = str_replace($match, end($replacements), $translation);
                }
            }
        }

        // We're done here.
        return $translation;
    }
}
