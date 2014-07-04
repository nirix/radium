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

namespace Radium\Language;

use Radium\Helpers\Time;

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
    public $translator;
    public $enumerator;

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
        // Use custom translator
        if ($this->translator) {
            return $this->translator($this->getString($string), $vars);
        }

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
        // Use custom enumerator
        if ($this->enumerator) {
            return $this->enumerator($numeral);
        }

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
