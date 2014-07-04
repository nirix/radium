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

namespace Radium\Util;

/**
 * Inflector class
 *
 * @author Jack P.
 * @package Radium
 * @subpackage Helpers
 */
class Inflector
{
    /**
     * Plural rules
     *
     * @var array
     */
    protected static $pluralRules = array(
        '/^(ox)$/i'                => '\1\2en',
        '/([m|l])ouse$/i'          => '\1ice',
        '/(matr|vert|ind)ix|ex$/i' => '\1ices',
        '/(x|ch|ss|sh)$/i'         => '\1es',
        '/([^aeiouy]|qu)y$/i'      => '\1ies',
        '/(hive)$/i'               => '\1s',
        '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
        '/sis$/i'                  => 'ses',
        '/([ti])um$/i'             => '\1a',
        '/(p)erson$/i'             => '\1eople',
        '/(m)an$/i'                => '\1en',
        '/(c)hild$/i'              => '\1hildren',
        '/(buffal|tomat)o$/i'      => '\1\2oes',
        '/(bu|campu)s$/i'          => '\1\2ses',
        '/(alias|status|virus)$/i' => '\1es',
        '/(octop)us$/i'            => '\1i',
        '/(ax|cris|test)is$/i'     => '\1es',
        '/s$/'                     => 's',
        '/$/'                      => 's',
    );

    /**
     * Singular rules
     *
     * @var array
     */
    protected static $singularRules = array(
        '/(matr)ices$/i'        => '\1ix',
        '/(vert|ind)ices$/i'    => '\1ex',
        '/^(ox)en/i'            => '\1',
        '/(alias)es$/i'         => '\1',
        '/([octop|vir])i$/i'    => '\1us',
        '/(cris|ax|test)es$/i'  => '\1is',
        '/(shoe)s$/i'           => '\1',
        '/(o)es$/i'             => '\1',
        '/(bus|campus)es$/i'    => '\1',
        '/([m|l])ice$/i'        => '\1ouse',
        '/(x|ch|ss|sh)es$/i'    => '\1',
        '/(m)ovies$/i'          => '\1\2ovie',
        '/(s)eries$/i'          => '\1\2eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i'         => '\1f',
        '/(tive)s$/i'           => '\1',
        '/(hive)s$/i'           => '\1',
        '/([^f])ves$/i'         => '\1fe',
        '/(^analy)ses$/i'       => '\1sis',
        '/([ti])a$/i'           => '\1um',
        '/(p)eople$/i'          => '\1\2erson',
        '/(m)en$/i'             => '\1an',
        '/(s)tatuses$/i'        => '\1\2tatus',
        '/(c)hildren$/i'        => '\1\2hild',
        '/(n)ews$/i'            => '\1\2ews',
        '/([^us])s$/i'          => '\1',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
    );

    /**
     * Converts the string to a class name.
     *
     * @param string  $string
     * @param boolean Also convert to singular form
     *
     * @return string
     */
    public static function classify($string, $singularise = false)
    {
        $class = ($singularise) ? static::singularise($string) : $string;
        return static::camelise($class);
    }

    /**
     * Converts the string to controller class name format.
     *
     * @param string $string
     *
     * @return string
     */
    public static function controllerise($string)
    {
        return static::camelise(static::pluralise($string));
    }

    /**
     * Converts the string to model class name format.
     *
     * @param string $string
     *
     * @return string
     */
    public static function modelise($string)
    {
        return static::camelise(static::singularise($string));
    }

    /**
     * Converts the string to a database table name.
     *
     * @param string $string
     *
     * @return string
     */
    public static function tablise($string)
    {
        return static::pluralise(static::underscore($string));
    }

    /**
     * Converts the string to CamelCase.
     *
     * @param string $string
     *
     * @return string
     */
    public static function camelise($string)
    {
        return preg_replace_callback(
            '/(^|_)(.)/',
            function($matches) {
                return strtoupper($matches[0]);
            },
            $string
        );
    }

    /**
     * Converts the string from CamelCase to under_score format.
     *
     * @param string $string
     *
     * @return string
     */
    public static function underscore($string)
    {
        return strtolower(preg_replace('/([A-Z]+)([A-Z])/', '\1_\2', preg_replace('/([a-z\d])([A-Z])/', '\1_\2', $string)));
    }

    /**
     * Converts the string into a foreign key.
     *
     * @param string $string
     *
     * @return string
     */
    public static function foreignKey($string)
    {
        return static::singularise(static::underscore($string)) . "_id";
    }

    /**
     * Converts the word to singular form.
     *
     * @param string $word
     *
     * @return string
     */
    public static function singularise($word)
    {
        // Run each rule over the word
        foreach (static::$singularRules as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return $word;
    }

    /**
     * Converts the word to plural form.
     *
     * @param string $word
     *
     * @return string
     */
    public static function pluralise($word)
    {
        // Run each rule over the word
        foreach (static::$pluralRules as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return $word;
    }
}
