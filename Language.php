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

namespace Radium;

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
    /**
     * Constructor!
     *
     * @param string $language
     */
    public function __construct($language)
    {
        $filePath = Loader::find("Translations\\{$language}", Loader::defaultNamespace());

        // Check if file exists
        if (file_exists($filePath)) {
            require $filePath;
            $this->language = new $language;
        } else {
            Error::halt('Translation Error', "Unable to load language file '<code>{$language}</code>'.");
        }
    }

    /**
     * Translates the specified string.
     *
     * @return string
     */
    public function translate($string, $vars = [])
    {
        return call_user_func_array([$this->language, 'translate'], func_get_args());
    }

    /**
     * Date localization method
     *
     * @param string $format
     * @param mixed  $timestamp
     */
    public function date($format, $timestamp = null)
    {
        return call_user_func_array([$this->language, 'date'], func_get_args());
    }

    /**
     * Adds extra locale strings.
     *
     * @param array $strings
     */
    public function add($strings)
    {
        $this->language->add($strings);
    }
}
